<?php
namespace App\Controller\Api;

use App\Entity\ChatMessage;
use App\Entity\ChatNotification;
use App\Entity\ChatParticipant;
use App\Entity\ChatThread;
use App\Entity\Relation;
use App\Entity\User;
use App\Services\CacheService;
use App\Services\Chat\ThreadService;
use App\Services\CRUDService;
use App\Services\UrlEncryptorService;
use App\UIBuilder\Chat\ThreadUI;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Cache\ItemInterface;

class ChatThreadController extends AbstractController {

    public function __construct(ThreadUI $threadUI,
                                CRUDService $CRUDService, ThreadService $threadService,
                                UrlEncryptorService $urlEncryptorService, CacheItemPoolInterface $cacheApp,
                                CacheService $cacheService, ManagerRegistry $doctrine, private readonly EntityManagerInterface $entityManager) {
        $this->threadUI = $threadUI;
        $this->CRUDService = $CRUDService;
        $this->threadService = $threadService;
        $this->urlEncryptorService = $urlEncryptorService;
        $this->cacheApp = $cacheApp;
        $this->cacheService = $cacheService;
        $this->doctrine = $doctrine;
    }


    #[Route('/chat/has-messages', methods: ['GET'])]
    public function loadNbNotifications(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'nbNotifications' => $this->doctrine->getRepository(ChatNotification::class)->countNotificationsOfUser($currentUser),
        ]);
        return $jsonResponse;
    }

    #[Route('/chat-get-thread-fromid', methods: ['GET', 'POST'])]
    public function getThreadFromId(Request $request) {
        $currentUser = $this->getUser();
        $thread = $this->doctrine->getRepository(ChatThread::class)->findOneBy([
            'id' => $request->get('threadId')
        ]);
        $threadUI = $this->threadUI->getThreadUI($thread, $currentUser->getId());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'thread' => $threadUI
        ]);
        return $jsonResponse;
    }

    #[Route('/chat-load-simple-thread', methods: ['GET'])]
    public function getThread(Request $request, CalEventUI $calEventUI) {
        $currentUser = $this->getUser();
        $user = $this->doctrine->getRepository(User::class)->findOneBy([
            'id' => $request->get('user')
        ]);
        if($request->get('user') == $currentUser->getId() || is_null($user)) {
            throw  new \ErrorException();
        }
        $currentThread = $this->threadService->getSimpleThreadFoUsers($currentUser, $user);
        $threadUI = $this->threadUI->getThreadUI($currentThread, $currentUser->getId());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'thread' => $threadUI
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/load-threads', methods: ['GET'])]
    public function loadThreads(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        $page = intval($request->get('page'));
        if($page <= 0) {
            $page = 1;
        }
        $maxResults = 20;
        $offset = ($page - 1)*$maxResults;
        $threads = $this->doctrine->getRepository(ChatThread::class)
            ->findThreadsOfUser($currentUser, $offset, $maxResults);
        $threadIdsWithNotifications = $this->doctrine->getRepository(ChatNotification::class)
            ->findNotificationsThreadIdsForUser($threads, $currentUser);
        $threadUIs = [];
        foreach ($threads as $thread) {
            $threadUIs[] = $this->threadUI->getThreadUI($thread, $currentUser->getId(), $threadIdsWithNotifications);
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'threads' => $threadUIs,
            'hasMoreThreads' => count($threadUIs) >= $maxResults
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/load-thread-relation', methods: ['GET'])]
    public function loadThreadForRelation(Request $request) {
        /** @var User $currentUser */
        $currentUser =  $this->getUser();
        $relation= $this->entityManager->getRepository(Relation::class)->findOneBy([
            'id' => $request->get('relation')
        ]);
        if(is_null($relation)) {
            throw  new \ErrorException();
        }
        $otherUser = $relation->getUserSource()->getId() == $currentUser->getId() ? $relation->getUserTarget() : $relation->getUserSource();
        $currentThread = $this->threadService->getSimpleThreadFoUsers($currentUser, $otherUser);
        if(!is_null($currentThread->getDeletedAt())) {
            $currentThread->setDeletedAt(null);
            $this->entityManager->persist($currentThread);
            $this->entityManager->flush();
        }
        $threadUI = $this->threadUI->getThreadUI($currentThread, $currentUser->getId());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'thread' => $threadUI,
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/load-modal-thread-datas', methods: ['GET'])]
    public function loadModalThreadDatas(Request $request) {
        $threadTypeChoices =  [
            [
                'val' => 'group',
                'icon' => 'iconido-chat-multiple',
                'title' => 'Créer un groupe de discussion',
                'description' => 'Discutez avec plusieurs membres de votre communauté'
            ]
        ];

        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'threadTypeChoices' => $threadTypeChoices,
            'infoSelection' => "Choisissez une liste pour la sélection de participants afin de créer la conversation.",
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/thread/load-list-to-add', methods: ['POST'])]
    public function loadUsersToAddOnThread(Request $request) {
        $currentUser =  $this->getUser();
        $crudParameters = $this->CRUDService->getListParametersFromRequest($request);
        $maxPerPage = $crudParameters['itemsPerPage'];
        $page = $request->get('page');
        $hasMoreResults = false;
        $threadId = $request->get('thread');
        $particpantUsersIds = [];
        $jsonResponse = new JsonResponse();
        if(!is_null($threadId)) {
            /** @var ChatThread $currentThread */
            $currentThread = $this->doctrine->getRepository(ChatThread::class)->findThread($threadId);
            if(!is_null($currentThread)) {
                if($currentThread->getCreatedBy()->getId() != $currentUser->getId()) {
                    throw new AccessDeniedException();
                }
                /** @var ChatParticipant $participants */
                $participants = $this->doctrine->getRepository(ChatParticipant::class)->findParticipants($currentThread);
                //ne pas ajouter de participants à une discussion simple !
                if(count($participants) >= 2 && $currentThread->getType() == 'simple') {
                    throw new AccessDeniedException();
                }
                foreach ($participants as $participant) {
                    $user = $participant->getUser();
                    $particpantUsersIds[] = $user->getId();
                }
            }
        }
        $search = $request->get('searchTerm');
        if(!is_null($search) && strlen($search) < 3) {
            $search = null;
        }
        $users = $this->doctrine->getRepository(Relation::class)
            ->findListOfRelationUsers($currentUser, ['active'], $maxPerPage, $page, $search);
        if(count($users) >= $maxPerPage) {
            $hasMoreResults = true;
        }
        $userUIs = $this->threadUI
            ->getAddUserList($users, $currentUser->getId(), $particpantUsersIds);
        $jsonResponse->setData([
            'users' => $userUIs,
            'hasMoreResults' => $hasMoreResults
        ]);
        return $jsonResponse;
    }

    #[Route('/chat/thread/add-user', methods: ['POST'])]
    public function addUserOnThread(Request $request) {
        $currentUser =  $this->getUser();
        $threadId = $request->get('thread') == 'null' ? null : intval($request->get('thread'));
        $threadType = $request->get('threadType');
        $userToAddIds = json_decode($request->get('userIds'), true);
        //check authorisation to add users !!
        $userToAdds = $this->threadService->getUserToAddAsParticpants($userToAddIds, $currentUser);
        $currentThread = $this->threadService->iniThread($threadId, $currentUser, $threadType, $userToAdds);
        $this->threadService->addParticipantsOnThread($currentThread, $userToAdds);
        $currentThread->setLastUpdate(new \DateTime('now'));
        $em = $this->doctrine->getManager();
        $em->persist($currentThread);
        $em->flush();
        $threadUI = $this->threadUI->getThreadUI($currentThread, $currentUser->getId());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'threadUI' => $threadUI
        ]);
        return $jsonResponse;
    }


    #[Route('/chat/thread/add-single-user', methods: ['POST'])]
    public function addSingleUserOnExistingThread(Request $request) {
        $currentUser =  $this->getUser();
        $threadId = intval($request->get('thread'));
        $userToAdd = $this->doctrine->getRepository(User::class)->findOneBy([
            'id' => $request->get('userId')
        ]);
        $thread = $this->doctrine->getRepository(ChatThread::class)->findOneBy([
            'id' =>  $threadId
        ]);
        $this->threadService->userCanManageThread($thread, $currentUser, true);
        $this->threadService->addParticipantsOnThread($thread, [$userToAdd]);
        $thread->setLastUpdate(new \DateTime('now'));
        $em = $this->doctrine->getManager();
        $em->persist($thread);
        $em->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }

    #[Route('/chat/thread/delete-user', methods: ['POST'])]
    public function deleteUserOnThread(Request $request) {
        $currentUser =  $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->doctrine->getRepository(ChatThread::class)->findThread($request->get('thread'));
        if($currentUser->getId() != $thread->getCreatedBy()->getId()) {
            throw new AccessDeniedException();
        }
        /** @var ChatParticipant $participant */
        $userId = $request->get('user');
        $participant = $this->doctrine->getRepository(ChatParticipant::class)->findParticipant($userId, $thread);
        if($thread->getType() != 'group' || $participant->getUser()->getId() == $thread->getCreatedBy()->getId()) {
            throw new \ErrorException();
        }
        $em = $this->doctrine->getManager();
        $thread->removeParticipant($participant);
        $em->remove($participant);
        $em->persist($thread);
        $em->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }

    #[Route('/chat/thread/edit-thread-group', methods: ['POST'])]
    public function editThreadhGroup(Request $request) {
        $currentUser = $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->doctrine->getRepository(ChatThread::class)->findThread($request->get('thread'));
        if($currentUser->getId() != $thread->getCreatedBy()->getId()) {
            throw new AccessDeniedException();
        }
        $name = $request->get('name') != null ? mb_substr($request->get('name'), 0, 80) : null;
        $thread->setName($name);
        $this->threadService->saveImageFromRequest($request, $thread);
        $em = $this->doctrine->getManager();
        $em->persist($thread);
        $em->flush();
        $threadUI = $this->threadUI->getThreadUI($thread, $currentUser->getId());
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'thread' => $threadUI
        ]);
        return $jsonResponse;
    }

    #[Route('/v-thread-leave-group', methods: ['POST'])]
    public function leaveGroupThread(Request $request) {
        $currentUser =  $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThread($request->get('thread'));
        /** @var ChatParticipant $participant */
        $userId = $currentUser->getId();
        $participant = $this->entityManager->getRepository(ChatParticipant::class)->findParticipant($userId, $thread);
        if($thread->getType() != 'group' || $participant->getUser()->getId() == $thread->getCreatedBy()->getId()) {
            throw new \ErrorException();
        }
        $this->entityManager->getRepository(ChatNotification::class)->deleteNotificationOfThreadForUser($thread, $currentUser);
        $cacheKey = $this->cacheService->chatNbNotificationKey($currentUser);
        $this->cacheApp->deleteItem($cacheKey);
        $thread->removeParticipant($participant);
        $this->entityManager->remove($participant);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();
        return new JsonResponse();
    }


    #[Route('/v-thread-delete', methods: ['POST'])]
    public function deleteThread(Request $request) {
        $currentUser =  $this->getUser();
        /** @var ChatThread $thread */
        $thread = $this->entityManager->getRepository(ChatThread::class)->findThread($request->get('thread'));
        if($thread->getType() == 'group') {
            $canManageThread = $this->threadService->userCanManageThread($thread, $currentUser, false);
        } else { //simple
            /** @var ChatParticipant $participant */
            $userId = $currentUser->getId();
            $participant = $this->entityManager->getRepository(ChatParticipant::class)->findParticipant($userId, $thread);
            $canManageThread = !is_null($participant);
        }
        $jsonResponse = new JsonResponse();
        if($canManageThread) {
            //remove notifs
            $this->entityManager->getRepository(ChatNotification::class)->deleteNotificationsOfThread($thread);
            $cacheKey = $this->cacheService->chatNbNotificationKey($currentUser);
            $this->cacheApp->deleteItem($cacheKey);
            $thread->setDeletedAt(new \DateTime('now'));
            $this->entityManager->persist($thread);
            $thread->setLastMessage(null);
            $this->entityManager->flush();
            //delete message
            $this->entityManager->getRepository(ChatMessage::class)->deleteMessagesOfThread($thread);
        } else {
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => 'Tu ne peux pas gérer cette converstation !'
            ]);
        }
        return $jsonResponse;
    }





}