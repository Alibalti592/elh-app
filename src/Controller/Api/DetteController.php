<?php

namespace App\Controller\Api;

use App\Entity\Location;
use App\Entity\Obligation;
use App\Entity\Relation;
use App\Entity\Testament;
use App\Entity\TestamentShare;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\UtilsService;
use App\UIBuilder\ObligationUI;
use App\UIBuilder\RelationUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;


class DetteController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly ObligationUI $obligationUI,
                                private readonly RelationUI $relationUI, private readonly NotificationService $notificationService) {}

#[Route('/load-dettes')]
public function loadDettes(Request $request): Response
{
    $currentUser = $this->getUser();
    $detteType = $request->get('detteType');
    $filter = $request->get('filter');

    $obligations = $this->entityManager->getRepository(Obligation::class)
        ->findObligationsOfUser($currentUser, $detteType, $filter);

    $detteTypeOpposite = $detteType;
    if ($detteType == 'onm') {
        $detteTypeOpposite = 'jed';
    } else if ($detteType == 'jed') {
        $detteTypeOpposite = 'onm';
    }

    $obligationsShared = $this->entityManager->getRepository(Obligation::class)
        ->findObligationsShared($currentUser, $detteTypeOpposite);

    $totalAmount = 0;
    $obligationUIs = [];

    foreach ($obligations as $obligation) {
        $obUI = $this->obligationUI->getObligation($obligation, true, $currentUser);

        // Add uploaded file URL if exists
        $obUI['fileUrl'] = $obligation->getFileUrl() ?? null;

        if ($filter == 'processing' || $filter == 'refund') {
            $totalAmount += floatval($obligation->getAmount());
        }

        $obligationUIs[] = $obUI;
    }

    $obligationSharedUIs = [];
    foreach ($obligationsShared as $obligation) {
        $obUI = $this->obligationUI->getObligation($obligation, false);
        $obUI['fileUrl'] = $obligation->getFileUrl() ?? null;
        $obligationSharedUIs[] = $obUI;
    }

    return new JsonResponse([
        'obligations' => $obligationUIs,
        'obligationsShared' => $obligationSharedUIs,
        'totalAmount' => round($totalAmount, 2)
    ]);
}


#[Route('/save-dette', methods: ['POST'])]
public function saveObligation(
    Request $request,
    ValidatorInterface $validator,
    UserRepository $userRepository
): JsonResponse {
    $currentUser = $this->getUser();
    if (!$currentUser) {
        return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
    }

    $obligationJson = $request->request->get('obligation');
    if (empty($obligationJson)) {
        return new JsonResponse(['error' => 'Payload manquant: obligation'], 400);
    }

    $data = json_decode($obligationJson, true);
    if (!is_array($data)) {
        return new JsonResponse(['error' => 'Payload invalide'], 400);
    }

    /** @var UploadedFile|null $uploadedFile */
    $uploadedFile = $request->files->get('file');
    if ($uploadedFile instanceof UploadedFile) {
        try {
            // Initialize Firebase Storage
            $storage = (new Factory())
                ->withServiceAccount(\dirname(__DIR__, 3) . '/config/firebase_credentials.json')
                ->withDefaultStorageBucket('mc-connect-5bd22') // bucket name only
                ->createStorage();

            $bucket = $storage->getBucket();

            // Build a unique path (optional "obligations/" folder)
            $ext = $uploadedFile->guessExtension() ?: 'bin';
            $fileName = sprintf('obligations/%s.%s', uniqid('obh_', true), $ext);

            // Upload the binary
            $bucket->upload(
                fopen($uploadedFile->getPathname(), 'r'),
                [
                    'name' => $fileName,
                    // If your bucket is NOT public by default, uncomment next line:
                    // 'predefinedAcl' => 'publicRead',
                ]
            );

            // Public URL (works if the object is publicly readable)
            $data['fileUrl'] = sprintf('https://storage.googleapis.com/%s/%s', $bucket->name(), $fileName);
        } catch (FirebaseException $e) {
            return new JsonResponse(['error' => 'Firebase error: ' . $e->getMessage()], 500);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }

    $obligation = new Obligation();
    $amount = isset($data['amount']) ? (string) $data['amount'] : '0';

    $obligation->setType($data['type'] ?? 'jed');
    $obligation->setAmount($amount);
    $obligation->setRemainingAmount((float) $amount);
    $obligation->setCreatedBy($currentUser);
    $obligation->setCreatedAt(new \DateTimeImmutable());
    $obligation->setTel($data['tel'] ?? null);
$obligation->setFirstname($data['firstname'] ?? null);
$obligation->setLastname($data['lastname'] ?? null);
    $obligation->setRaison($data['note'] ?? ($data['raison'] ?? null));

    $obligation->setStatus($data['status'] ?? 'ini');
    $obligation->setDateStart(!empty($data['dateStart']) ? new \DateTime($data['dateStart']) : null);
    $obligation->setDate(!empty($data['date']) ? new \DateTime($data['date']) : null);
    $obligation->setFileUrl($data['fileUrl'] ?? null);

    if (!empty($data['relatedUserId'])) {
        $relatedUser = $userRepository->find($data['relatedUserId']);
        if ($relatedUser) {
            $obligation->setRelatedTo($relatedUser);
        }
    }

    $errors = $validator->validate($obligation);
    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }
        return new JsonResponse(['errors' => $errorMessages], 400);
    }

    $this->entityManager->persist($obligation);
    $this->entityManager->flush();

    return new JsonResponse([
        'message' => 'Dette enregistrée avec succès',
        'obligationId' => $obligation->getId(),
        'fileUrl' => $obligation->getFileUrl(), // convenient echo
    ], 200);
}



    #[Route('/delete-dette', methods: ['POST'])]
    public function deleteObligation(Request $request): Response
    {
        $currentUser = $this->getUser();
        $obligation = $this->entityManager->getRepository(Obligation::class)->findOneBy([
            'id' => $request->get('obligationId'),
        ]);
        if(!is_null($obligation)) {
            if($currentUser->getId() != $obligation->getCreatedBy()->getId() && $currentUser->getId() != $obligation->getRelatedTo()->getId()) {
                throw new \ErrorException("cant deleteObligation");
            }
            $obligation->setDeletedAt(new \DateTimeImmutable());
            $this->entityManager->persist($obligation);
            $this->entityManager->flush();
        } else {
            throw new \ErrorException("deleteObligation");
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/refund-dette', methods: ['POST'])]
    public function refundObligation(Request $request): Response
    {
        $currentUser = $this->getUser();
        /** @var Obligation $obligation */
        $obligation = $this->entityManager->getRepository(Obligation::class)->findObligationCanRefund($currentUser, $request->get('obligationId'));
        if(!is_null($obligation)) {
            $notifUser = false;
            if($request->get('refundBack') == 'true') {
                $obligation->setStatus('ini');
            } else {
                $obligation->setStatus('refund');
                if(!is_null($obligation->getRelatedTo())) {
                    //notify other part
                    $notifUser = true;
                }
            }
            $this->entityManager->persist($obligation);
            $this->entityManager->flush();
            if($notifUser) {
                $this->notificationService->notifRefundDette($currentUser, $obligation);
            }
        } else {
            throw new \ErrorException("refundObligation");
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/dette-set-relatedto', methods: ['POST'])]
    public function setRelatedTo(Request $request): Response {
        $currentUser = $this->getUser();
        $obligation = $this->entityManager->getRepository(Obligation::class)->findOneBy([
            'id' => $request->get('obligationId'),
            'createdBy' => $currentUser
        ]);
        $jsonResponse = new JsonResponse();
        if(!is_null($obligation)) {
            $userRelated = $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $request->get('userId')
            ]);
            if(!is_null($userRelated)) {
                $relation = $this->entityManager->getRepository(Relation::class)->findRelation($currentUser, $userRelated);
                if(!is_null($relation)) {
                    $obligation->setRelatedTo($userRelated);
                    $this->entityManager->persist($obligation);
                    $this->entityManager->flush();
                    $this->notificationService->notifForNewObligation($obligation);
                } else {
                    $jsonResponse->setStatusCode(500);
                    $jsonResponse->setData([
                        'message'   => 'Ce membre ne fait pas partie de votre communauté'
                    ]);
                }
            } else {
                $jsonResponse->setStatusCode(500);
                $jsonResponse->setData([
                    'message'   => 'Utilisateur introuvable'
                ]);
            }
        } else {
            throw new \ErrorException("relatedto err");
        }
        return $jsonResponse;
    }

    #[Route('/load-dettes-to-refund', methods: ['GET'])]
    public function loadDettesToRefund(Request $request): Response
    {
        $currentUser = $this->getUser();
        $obligations = $this->entityManager->getRepository(Obligation::class)
            ->findObligationToRefund($currentUser);
        $obligationUIs = [];
        foreach ($obligations as $obligation) {
            $obligationUIs[] = $this->obligationUI->getObligation($obligation, true, $currentUser);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'obligations' => $obligationUIs,
        ]);
        return $jsonResponse;
    }


    #[Route('/load-all-user-dettes-not-refund', methods: ['GET'])]
    public function loadAllDettesNotRefund(Request $request): Response
    {
        $currentUser = $this->getUser();
        $obligations = $this->entityManager->getRepository(Obligation::class)
            ->findObligationNotRefund($currentUser);
        $obligationUIs = [];
        foreach ($obligations as $obligation) {
            $obligationUIs[] = $this->obligationUI->getObligation($obligation, true, $currentUser);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'obligations' => $obligationUIs,
        ]);
        return $jsonResponse;
    }
}