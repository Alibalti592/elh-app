<?php

namespace App\Controller\Api;

use App\Entity\FcmToken;
use App\Entity\Notification;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController {

    private $notificationService;

    public function __construct(NotificationService $notificationService, private readonly EntityManagerInterface $entityManager) {
        $this->notificationService = $notificationService;
    }

    #[Route('/notification/has-message', methods: ['GET'])]
    public function hasMessage(Request $request) {
        $user = $this->getUser();
        $jsonReponse = new JsonResponse();
        $chatNotif = $this->entityManager->getRepository(Notification::class)->findOneBy([
            'createdFor' => $user,
            'type' => 'chat'
        ]);
        $jsonReponse->setData(
            is_null($chatNotif) ? false : true
        );
        return $jsonReponse;
    }


    #[Route('/notification/post-fcm-token', methods: ['POST'])]
    public function saveFCMToken(Request $request) {
        $user = $this->getUser();
        $data = json_decode($request->getContent());
        $fcmToken = $data->fcmToken;
        $deviceId = $data->deviceId ?? null;
        //find old tokens, keep 4 max !! (v1 pbs ajout)
        $allTokens = $this->entityManager->getRepository(FcmToken::class)->findAllTokensOfUser($user);
        $numToken = 0;
        /** @var FcmToken $fToken */
        foreach ($allTokens as $fToken) {
            $toDelete = false;
            if(!is_null($deviceId) && $deviceId != '' && $fToken->getDeviceId() == $deviceId) {
                $toDelete = true;
            }
            if($numToken > 3 || $toDelete) {
                $this->entityManager->remove($fToken);
            }
            $numToken++;
        }
        $this->entityManager->flush();
        /** @var FcmToken $alreadyExistToken */
        $alreadyExistToken = $this->entityManager->getRepository(FcmToken::class)->findOneBy([
            'fcmToken' => $fcmToken,
            'user' => $user
        ]);
        if(is_null($alreadyExistToken)) {
            $newFCMToken = new FcmToken();
            $newFCMToken->setUser($user);
            $newFCMToken->setFcmToken($fcmToken);
            $newFCMToken->setDeviceId($deviceId);
            $this->entityManager->persist($newFCMToken);
            $this->entityManager->flush();
        } elseif (!is_null($deviceId) && is_null($alreadyExistToken->getDeviceId())) {
            $alreadyExistToken->setDeviceId($deviceId);
            $this->entityManager->persist($alreadyExistToken);
            $this->entityManager->flush();
        }
        return new JsonResponse();
    }

    #[Route('/notification/delete-fcm-token', methods: ['POST'])]
    public function deleteFCMToken(Request $request) {
        $user = $this->getUser();
        $data = json_decode($request->getContent());
        $fcmToken = $data->fcmToken;
        //le user peut avoir changé donc on supprime peu importe user !!
        $existingTokens = $this->entityManager->getRepository(FcmToken::class)->findBy([
            'fcmToken' => $fcmToken,
        ]);
        foreach ($existingTokens as $existingToken) {
            $this->entityManager->remove($existingToken);
            $this->entityManager->flush();
        }
        return new JsonResponse();
    }

}
