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
use Symfony\Component\HttpFoundation\Response;
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
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $fcmToken = trim((string)($data['fcmToken'] ?? ''));
        $deviceId = trim((string)($data['deviceId'] ?? ''));
        if($deviceId === '') {
            $deviceId = null;
        }
        if($fcmToken === '') {
            return new JsonResponse(['error' => 'Missing fcmToken'], Response::HTTP_BAD_REQUEST);
        }

        // one FCM token must belong to one user only
        $sameValueTokens = $this->entityManager->getRepository(FcmToken::class)->findBy([
            'fcmToken' => $fcmToken,
        ]);
        foreach ($sameValueTokens as $sameValueToken) {
            $this->entityManager->remove($sameValueToken);
        }
        $this->entityManager->flush();

        $newFCMToken = new FcmToken();
        $newFCMToken->setUser($user);
        $newFCMToken->setFcmToken($fcmToken);
        $newFCMToken->setDeviceId($deviceId);
        $this->entityManager->persist($newFCMToken);
        $this->entityManager->flush();

        // find old tokens, keep 4 max and keep one token by device id for this user
        $allTokens = $this->entityManager->getRepository(FcmToken::class)->findAllTokensOfUser($user);
        $numToken = 0;
        /** @var FcmToken $fToken */
        foreach ($allTokens as $fToken) {
            $isCurrentToken = $fToken->getId() === $newFCMToken->getId();
            $toDelete = false;
            if(!$isCurrentToken && !is_null($deviceId) && $fToken->getDeviceId() === $deviceId) {
                $toDelete = true;
            }
            if(!$isCurrentToken && $numToken > 3) {
                $toDelete = true;
            }
            if($toDelete) {
                $this->entityManager->remove($fToken);
            } else {
                $numToken++;
            }
        }
        $this->entityManager->flush();

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
