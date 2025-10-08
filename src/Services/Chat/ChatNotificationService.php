<?php
namespace App\Services\Chat;

use App\Entity\ChatMessage;
use App\Entity\ChatNotification;
use App\Entity\ChatParticipant;
use App\Entity\ChatThread;
use App\Entity\FcmToken;
use App\Services\CacheService;
use App\Services\FcmNotificationService;
use Doctrine\ORM\EntityManagerInterface;

class ChatNotificationService {

    public function __construct(EntityManagerInterface $entityManager,
                                FcmNotificationService $fcmNotificationService,
                                CacheService $cacheService) {
        $this->entityManager = $entityManager;
        $this->fcmNotificationService = $fcmNotificationService;
        $this->cacheService = $cacheService;
    }

    public function addNotificationsForMessage(ChatMessage $chatMessage, ChatThread $thread) {
        $participants = $this->entityManager->getRepository(ChatParticipant::class)->findParticipants($thread);
        $chatNotificationsOfThread = $this->entityManager->getRepository(ChatNotification::class)->findNotificationsOfThread($thread);
        $messageUserId = $chatMessage->getCreatedBy()->getId();
        $usersToSendFCM = [];
        foreach ($participants as $participant) {
            $user = $participant->getUser();
            $userId = $user->getId();
            if($messageUserId != $userId) { //pas de notif pour le sender
                $chatNotificationExist = null;
                $sendFCM = true;
                if(isset($chatNotificationsOfThread[$userId])) {
                    $chatNotificationExist = $chatNotificationsOfThread[$userId];
                    $sendFCM = $this->hasToSendFCM($chatNotificationExist->getUpdatedAt());
                }
                $this->setNotification($thread, $user, $chatNotificationExist);
                if($sendFCM) {
                    $usersToSendFCM[] = $user;
                }
                $this->cacheService->onChatNotificationUpdate($user);
            }
        }
        $this->entityManager->flush();
        $this->sendFcmChatNotification($usersToSendFCM, $chatMessage, $thread);
    }

    public function setNotification($thread, $user, $chatNotification) {
        if(is_null($chatNotification)) {
            $chatNotification = new ChatNotification();
            $chatNotification->setThread($thread);
            $chatNotification->setUser($user);
        }
        $chatNotification->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->entityManager->persist($chatNotification);
    }

    //send FCM notif if needed (pas plus de toute les 60 secondes)
    public function hasToSendFCM($lastUpdatedAt) {
        $lastNotifCreatedAtTimeStamp = $lastUpdatedAt->getTimestamp();
        $now = new \DateTime('now');
        $nowTimeStamp = $now->getTimestamp();
        if($nowTimeStamp - $lastNotifCreatedAtTimeStamp < 30) {
            return false;
        }
        return true;
    }

    public function sendFcmChatNotification($users, ChatMessage $chatMessage, ChatThread $thread) {
        //FCM token
        $fcmTokens = $this->entityManager->getRepository(FcmToken::class)->findTokensOfUsers($users);
        $this->fcmNotificationService->sendFcmChatNotification($fcmTokens, $chatMessage);
    }

    public function clearNotifications(ChatThread $thread, $currentUser) {
        $this->entityManager->getRepository(ChatNotification::class)
            ->deleteNotificationOfThreadForUser($thread, $currentUser);
        $this->cacheService->onChatNotificationUpdate($currentUser);
    }

}