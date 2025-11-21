<?php
namespace App\Controller\Api;

use App\Entity\Invitation;
use App\Entity\Location;
use App\Entity\Media;
use App\Entity\RefreshToken;
use App\Entity\Resetpassword;
use App\Entity\User;
use App\Services\CsrfTokenService;
use App\Services\RelationService;
use App\Services\S3Service;
use App\Services\UserService;
use App\UIBuilder\UserUI;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UserController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UserUI $userUI,
                                private readonly CsrfTokenService $csrfTokenService, private readonly S3Service $s3Service,
                                private readonly RelationService $relationService, private readonly LoggerInterface $logger, private readonly UserService $userService) {}

    #[Route('/get-user-infos')]
    public function getUserInfos(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $userInfos = $this->userUI->getUserProfilUI($user);
        $jsonReponse = new JsonResponse();
        $jsonReponse->setData($userInfos);
        return $jsonReponse;
    }

    #[Route('/user-registration', methods: ['POST'])]
    public function userRegistrationAction(Request $request,UserPasswordHasherInterface $userPasswordHasher) {
        $data = json_decode($request->getContent());
        $userRegistration = $data->userRegistration;
        $jsonReponse = new JsonResponse();
        $alreadyExist = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userRegistration->email]);
      
        if(!is_null($alreadyExist)) {
            $jsonReponse->setData([
                'message' => 'Ce compte existe déjà !'
            ]);
            $jsonReponse->setStatusCode(409);
            return $jsonReponse;
        }
       
        try {
            $user = new User();
            $user->setEmail($userRegistration->email);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userRegistration->password
                )
            );
            $user->setFirstname($userRegistration->firstname);
            $user->setLastname($userRegistration->lastname);
            $user->setPhone($userRegistration->phone);
            $user->setPhonePrefix($userRegistration->phonePrefix);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            //check si invitation
            $invitations = $this->entityManager->getRepository(Invitation::class)->findExistingInvitations($user->getEmail());
            $relationUserIdAdded = [];
            /** @var Invitation $invitation */
            foreach ($invitations as $invitation) {
                $userInvit = $invitation->getCreatedBy();
                if(!in_array($userInvit->getId(), $relationUserIdAdded)) {
                    $relationUserIdAdded[] = $userInvit->getId();
                    $this->relationService->defineRelation($user, $userInvit, 'active');
                    $invitation->setAccpeted(true);
                    $this->entityManager->persist($invitation);
                    $this->entityManager->flush();
                }
            }
            $jsonReponse->setStatusCode(200);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $jsonReponse->setData([
                'message' => "Un erreur s'est produite, merci de nous contacter si le problème persiste. "
            ]);
            $jsonReponse->setStatusCode(500);
        }
        return $jsonReponse;
    }

    #[Route('/delete-refresh-token', methods: ['POST'])]
    public function deleteRefreshToken(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent());
        if(!is_null($data) && isset($data->refreshTokenString)) {
            $refreshTokenString = $data->refreshTokenString;
            //le user peut avoir changé donc on supprime peu importe user !!
            $existingTokens = $this->entityManager->getRepository(RefreshToken::class)->findBy([
                'refreshToken' => $refreshTokenString,
                "username" => $user->getEmail()
            ]);
            foreach ($existingTokens as $existingToken) {
                $this->entityManager->remove($existingToken);
                $this->entityManager->flush();
            }
        }
        return new JsonResponse();
    }


    #[Route('/user-save-account', methods: ['POST'])]
    public function saveUserAccount(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $userInfos = json_decode($request->get('userInfos'), true);
        $user->setFirstname($userInfos['firstname']);
        $user->setLastname($userInfos['lastname']);
        $user->setPhone($userInfos['phone']);
        $user->setPhonePrefix($userInfos['phonePrefix']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->userUI->clearUserUI($user);
        $jsonResponse = new JsonResponse();
        //location
        if(!is_null($request->get('location'))) {
            $newLocation = json_decode($request->get('location'), true);
            $location = $user->getLocation();
            if(is_null($location)) {
                $location = new Location();
                $user->setLocation($location);
            }
            $location->setLabel($newLocation['label']);
            if(isset($newLocation['adress'])) {
                $location->setAdress($newLocation['adress']);
            }
            $location->setCity($newLocation['city']);
            $location->setLat(floatval($newLocation['lat']));
            $location->setLng(floatval($newLocation['lng']));
            $location->setPostCode($newLocation['postcode']);
            $location->setRegion($newLocation['region']);
            $this->entityManager->persist($user);
            $this->entityManager->persist($location);
            $this->entityManager->flush();
            $this->userUI->clearUserUI($user);
        }
        if(!is_null($request->get('newEmail'))) {
            $oldMail = $user->getEmail();
            $newEmail = $request->get('newEmail');
            if($oldMail != $newEmail) {
                $newEmail = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
                if(!$newEmail) {
                    $jsonResponse->setStatusCode(500);
                    $jsonResponse->setData([
                        'message' => 'Email non valide !'
                    ]);
                    return $jsonResponse;
                }
                $exist = $this->entityManager->getRepository(User::class)->findOneBy([
                    'email' => $newEmail
                ]);
                if(!is_null($exist)) {
                    $jsonResponse->setStatusCode(500);
                    $jsonResponse->setData([
                        'message' => 'Compte déjà existant avec cette adresse !'
                    ]);
                    return $jsonResponse;
                }
                $user->setEmail($newEmail);
                $newTokensArr = $this->csrfTokenService->createNewJWT($user);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $jsonResponse->setData([
                    'token' => $newTokensArr['newToken']
                ]);
                return $jsonResponse;
            }
        }
        return new JsonResponse();
    }


    #[Route('/update-image-profile')]
    public function updatePhoto(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $base64Profile = $request->get('base64Profile');
        if(!is_null($base64Profile) and $base64Profile != "") {
            $bucket = $this->s3Service->getMainBucket();
            $orignialImage = $this->s3Service->getImageFromBase64($base64Profile);
            $optimizedImagePath = $this->s3Service->optimizeImageBeforeUpload($orignialImage, 200, 200, true);
            $folder = 'user';
            $prefix = 'photo-u'.$user->getId().'_';
            $fileName = $this->s3Service->saveJpegFromLocalTmpPath($bucket, $folder, $optimizedImagePath, 'public-read', $prefix);
            unlink($optimizedImagePath);
            $type = 'image/jpeg';
            $image = $user->getPhoto();
            if(is_null($image)) {
                $image = new Media();
                $user->setPhoto($image);
            } else {
                $this->s3Service->deleteFileFromMedia($image);
            }
            $image->setFilename($fileName);
            $image->setBucket($bucket);
            $image->setFolder($folder);
            $image->setType($type);
            $image->setVersion(time());
            $this->entityManager->persist($image);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $this->userUI->clearUserUI($user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/remove-image-profile')]
    public function removePhoto(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $image = $user->getPhoto();
        if(!is_null($image)) {
            $this->s3Service->deleteFileFromMedia($image);
            $user->setPhoto(null);
            $this->entityManager->remove($image);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/delete-account-validation')]
    public function deleteAccount(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        $this->userService->deleteUserAccount($user);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }
#[Route('/test-api/sign-in-with-google-flutter', name: 'app_api_user_registergoogleflutter_testapi', methods: ['POST'])]
public function registerGoogleFlutter(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $email = $data['email'] ?? null;

    $jsonResponse = new JsonResponse();

    if (!$email) {
        $jsonResponse->setStatusCode(400);
        $jsonResponse->setData(['message' => 'Email is required']);
        return $jsonResponse;
    }

    $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

    if (is_null($user)) {
        $jsonResponse->setStatusCode(404);
        $jsonResponse->setData(['message' => "Compte introuvable"]);
        return $jsonResponse;
    }

    // optional: block login for deleted/disabled users (you already use UserChecker for other flows)
    if (!is_null($user->getDeletedAt()) || !$user->isEnabled()) {
        $jsonResponse->setStatusCode(403);
        $jsonResponse->setData(['message' => "Compte bloqué"]);
        return $jsonResponse;
    }

    try {
        // Reuse your existing CsrfTokenService which already creates JWT for a user in your codebase
        $tokens = $this->csrfTokenService->createNewJWT($user);

        // createNewJWT seems to return an array (you used ['newToken'] previously). Be flexible:
        $tokenString = $tokens['newToken'] ?? $tokens['token'] ?? null;
        $refreshTokenString = $tokens['refreshToken'] ?? $tokens['refresh_token'] ?? null;

        $responseData = [
            'token' => $tokenString,
            // include refresh token if present
            'refreshToken' => $refreshTokenString,
            // include basic user UI payload (optional)
            'user' => $this->userUI->getUserProfilUI($user),
        ];

        $jsonResponse->setData($responseData);
        return $jsonResponse;
    } catch (\Exception $e) {
        $this->logger->error('RegisterGoogleFlutter error: '.$e->getMessage());
        $jsonResponse->setStatusCode(500);
        $jsonResponse->setData(['message' => "Une erreur s'est produite, merci de réessayer plus tard."]);
        return $jsonResponse;
    }
}


}
