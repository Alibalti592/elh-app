<?php
namespace App\Controller\Api;

use App\Entity\ChatMessage;
use App\Entity\ChatParticipant;
use App\Entity\ChatThread;
use App\Entity\Coaching\ShareCalendarEvent;
use App\Entity\User;
use App\Services\Chat\ChatNotificationService;
use App\Services\Chat\ThreadService;
use App\Services\MercureHubService;
use App\Services\UrlEncryptorService;
use App\UIBuilder\Chat\MessageUI;
use App\UIBuilder\Chat\ThreadUI;
use App\UIBuilder\UserUI;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChatMessageController extends AbstractController {


    public function __construct(ThreadUI $threadUI, ThreadService $threadService, MessageUI $messageUI,
                                UrlEncryptorService $urlEncryptorService,
                                MercureHubService $mercureHubService, ChatNotificationService $chatNotificationService,
                                private readonly EntityManagerInterface $entityManager, private readonly RateLimiterFactory $chatMessageLimiter) {
        $this->threadUI = $threadUI;
        $this->threadService = $threadService;
        $this->messageUI = $messageUI;
        $this->urlEncryptorService = $urlEncryptorService;
        $this->mercureHubService = $mercureHubService;
        $this->chatNotificationService = $chatNotificationService;
    }

    #[Route('/chat/load-last-messages', methods: ['GET'])]
    public function loadLastMessages(Request $request) {
        $lastMessageId = null;
        $lastMessageIdDecrypt = null;
        if(!is_null($request->get('lastMessageId'))) {
            $lastMessageIdDecrypt = intval($this->messageUI->getMessageId($request->get('lastMessageId')));
        }
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThread($request->get('thread'));
        $this->threadService->userCanChatOnThread($thread, $currentUser, true);
        $currentUserId = $currentUser->getId();
        $messages = $this->entityManager->getRepository(ChatMessage::class)->findlastMessages($thread, $lastMessageIdDecrypt);
        $messageUIs = $this->messageUI->getMessageUIs($messages, $currentUserId, $thread->getId());
        $this->chatNotificationService->clearNotifications($thread, $currentUser);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'messages' => $messageUIs,
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/load-thread-messsages', methods: ['GET'])]
    public function loadMessages(Request $request, UserUI $userUI) {
        $page = intval($request->get('page'));
        $loadParticipants = $request->get('loadParticipants') == 'true';
        $limit = 15;
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThread($request->get('thread'));
        $this->threadService->userCanChatOnThread($thread, $currentUser, true);
        //clear notification
        $this->chatNotificationService->clearNotifications($thread, $currentUser);
        $currentUserId = $currentUser->getId();
        $participantUIs = null;
        if($loadParticipants) {
            $participants = $this->entityManager->getRepository(ChatParticipant::class)->findParticipants($thread);
            $participantUIs = $this->threadUI->getThreadParticipants($participants, $thread->getId());
        }
        $messages = $this->entityManager->getRepository(ChatMessage::class)->findMessages($thread, $page, $limit);
        $messageUIs = $this->messageUI->getMessageUIs($messages, $currentUserId, $thread->getId());
        $links = [];
//        $hubUrl = $this->mercureHubService->getHubSubscribeUrl("thread/".$thread->getId());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'participants' => $participantUIs,
            'messages' => $messageUIs,
            'userId' => $this->urlEncryptorService->getEncryptedUserId($currentUser->getId(), $thread->getId()),
            'hubUrl' => null,
            'hasMoreMessages' => count($messageUIs) >= $limit,
            'links' => $links
        ]);
        return $jsonResponse;
    }



    #[Route('/chat/send-messsage', methods: ['POST'])]
    public function sendMessage(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        $limiter = $this->chatMessageLimiter->create($currentUser->getId());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new \ErrorException('Flooding chat '.$currentUser->getId());
        }
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThread($request->get('thread'));
        $this->threadService->userCanChatOnThread($thread, $currentUser, true);
        $message = new ChatMessage();
        //check message length ?!
        $text = $request->get('text');
        if((is_null($text) || strlen($text) == 0) && $request->get('type') == 'text') {
            throw new \ErrorException('Empty message chat '.$currentUser->getId());
        }
        $text = strlen($text) > 500 ? mb_substr($text, 0, 500) : $text;
        $message->setContent(trim($text));
        $message->setChatThread($thread);
        $message->setCreatedBy($currentUser);
        $thread->setLastMessage($message);
        $thread->setLastUpdate(new \DateTime('now'));
        $this->entityManager->persist($message);
        $this->entityManager->persist($thread);
        $jsonResponse = new JsonResponse();
        if ($request->get('type') == 'file') {
            $fileName = $request->get('filename');
            $base64 = $request->get('base64');
            $volume = $this->entityManager->getRepository(ChatMessage::class)->getVolumeUploadedForUser($currentUser);
            if($volume >= 20) {
                $this->logger->error('User '.$currentUser->getEmail().' has exceeded uploaded 24h volume '.$volume.'MB');
                $jsonResponse->setStatusCode(500);
                $jsonResponse->setData([
                    'message' => "Désolé, tu as dépassé le quota d'upload de 20M pour aujourd'hui. Contact nous si c'est un problème !",
                ]);
                return $jsonResponse;
            }
            $isUploaded = $this->messageUI->uploadFile($message, $fileName, $base64);
            if(!$isUploaded) {
                $jsonResponse->setStatusCode(500);
                $jsonResponse->setData([
                    'message' => "Désolé, le fichier ne peut pas être uploadé !",
                ]);
                return $jsonResponse;
            }
        }
        $this->entityManager->flush();
        //async serait mieux
        $this->chatNotificationService->addNotificationsForMessage($message, $thread);
        $message = $this->messageUI->getMessageUI($message, $currentUser->getId(), $currentUser->getId(), $thread->getId());
        //notif new message
        $userEncryptID =$this->urlEncryptorService->getEncryptedUserId($currentUser->getId(), $thread->getId());
        $data = ['type' => 'newMessage', 'value' => $userEncryptID];
        //$this->mercureHubService->sendNotification("thread/".$thread->getId(), $data);
        $jsonResponse->setData([
            'message' => $message,
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/notify-thread', methods: ['POST'])]
    public function notifyTyping(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThread($request->get('thread'));
        $notifType = $request->get('type');
        $this->threadService->userCanChatOnThread($thread, $currentUser, true);
        $userTypingID = $notifType == 'typing' ?
            $this->urlEncryptorService->getEncryptedUserId($currentUser->getId(), $thread->getId()) : "";
        $data = [
            'type' => 'typing',
            'value' => $userTypingID
        ];
//        $this->mercureHubService->sendNotification("thread/".$thread->getId(), $data);
        return new JsonResponse();
    }


    #[Route('/v-chat-delete-msg', methods: ['POST'])]
    public function deleteMessage(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        $messageIdEncrypt = $request->get('message');
        $messageId = explode('-',$this->urlEncryptorService->decrypt($messageIdEncrypt))[1];
        $message = $this->entityManager->getRepository(ChatMessage::class)->findOneBy([
            'id' => $messageId,
            'createdBy' => $currentUser->getId(),
        ]);
        $jsonResponse = new JsonResponse();
        if(!is_null($message)) {
            $message->setContent("");
            $message->setDeletedAt(new \DateTime('now'));
            $this->messageUI->deleteMessageFile($message);
            $this->messageUI->deleteMessageCache($message);
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            $currentUserId = $currentUser->getId();
            $threadId = $message->getChatThread()->getId();
            //notif delete message
            $userEncryptID =$this->urlEncryptorService->getEncryptedUserId($currentUser->getId(), $threadId);
            $data = ['type' => 'newMessage', 'value' => $userEncryptID]; //newMessage ok car reload tout, on ourrait passer message !
//            $this->mercureHubService->sendNotification("thread/".$threadId, $data);
            $jsonResponse->setData([
                'message' => 'Message supprimé',
                'chatMessage' => $this->messageUI->getMessageUI($message, $currentUserId, $currentUserId, $threadId),
            ]);
        } else {
            $jsonResponse->setStatusCode(500);
        }
        return $jsonResponse;
    }


    #[Route('/v-chat-edit-messsage', methods: ['POST'])]
    public function editMessage(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        $messageUI = json_decode($request->get('message'), true);
        $messageId = explode('-',$this->urlEncryptorService->decrypt($messageUI['id']))[1];
        $message = $this->entityManager->getRepository(ChatMessage::class)->findOneBy([
            'id' => $messageId,
            'createdBy' => $currentUser->getId(),
        ]);
        $jsonResponse = new JsonResponse();
        if(!is_null($message)) {
            if(isset($messageUI['data']['text'])) {
                $text = $messageUI['data']['text']; //web
            } else {
                $text = $messageUI['message']['text']; //mobile
            }
            if(is_null($text) || strlen($text) == 0) {
                throw new \ErrorException('Empty message chat '.$currentUser->getId());
            }
            $text = strlen($text) > 500 ? mb_substr($text, 0, 500) : $text;
            $message->setContent($text);
            $message->setUpdatedAt(new \DateTime('now'));
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            $this->messageUI->deleteMessageCache($message);
            //notif edit message
            $threadId = $message->getChatThread()->getId();
            $userEncryptID =$this->urlEncryptorService->getEncryptedUserId($currentUser->getId(), $threadId);
            $data = ['type' => 'newMessage', 'value' => $userEncryptID]; //newMessage ok car reload tout, on ourrait passer message !
//            $this->mercureHubService->sendNotification("thread/".$threadId, $data);
            $currentUserId = $currentUser->getId();
            $jsonResponse->setData([
                'message' => 'Message supprimé',
                'chatMessage' => $this->messageUI->getMessageUI($message, $currentUserId, $currentUserId, $threadId),
            ]);
        } else {
            $jsonResponse->setStatusCode(500);
        }
        return $jsonResponse;
    }
}