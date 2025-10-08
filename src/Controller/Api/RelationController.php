<?php

namespace App\Controller\Api;

use App\Entity\ChatNotification;
use App\Entity\ChatThread;
use App\Entity\Invitation;
use App\Entity\Relation;
use App\Entity\User;
use App\Services\CacheService;
use App\Services\CRUDService;
use App\Services\NotificationService;
use App\Services\RelationService;
use App\UIBuilder\Chat\ThreadUI;
use App\UIBuilder\RelationUI;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RelationController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly RelationService $relationService,
                                private readonly CRUDService $CRUDService, private readonly RelationUI $relationUI,
                                private readonly NotificationService $notificationService, private readonly ThreadUI $threadUI,
                                private readonly CacheService $cacheService, private readonly CacheItemPoolInterface $cacheApp) {}

    #[Route('/load-relations')]
    public function loadList(Request $request): Response
    {
        $currentUser = $this->getUser();
        $relationsToValidate = $this->entityManager->getRepository(Relation::class)->findRelationsToValidate($currentUser);
        $relationsToValidateUI = $this->relationUI->getRelationsListToValidate($relationsToValidate);
        $search = $request->get('search');
        if($search == "") {
            $search = null;
        }
        $relations = $this->entityManager->getRepository(Relation::class)->findListOfRelations($currentUser, ['active'], 250, $search);
        $nbRelations = $this->entityManager->getRepository(Relation::class)->countActiverRelations($currentUser);
        $relationsUI = $this->relationUI->getRelationsList($relations, $currentUser);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'relationsToValidate' => $relationsToValidateUI,
            'relations' => $relationsUI,
            'nbRelations' => $nbRelations,
        ]);
        return $jsonResponse;
    }

    #[Route('/load-active-relations')]
    public function loadActiveContact(Request $request): Response
    {
        $currentUser = $this->getUser();
        $relations = $this->entityManager->getRepository(Relation::class)->findListOfRelations($currentUser, ['active'], 150);
        $nbRelations = $this->entityManager->getRepository(Relation::class)->countActiverRelations($currentUser);
        $relationsUI = $this->relationUI->getRelationsList($relations, $currentUser);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'relations' => $relationsUI,
            'nbRelations' => $nbRelations,
        ]);
        return $jsonResponse;
    }

    
    #[Route('/search-relations', methods: ['POST'])]
    public function searchRelations(Request $request, LoggerInterface $logger) {
        $currentUser =  $this->getUser();
        $search = trim(strtolower($request->get('search')));
        $searchedRelationsListUI = [];
        $doNotSearch = false;
        $searchBy = 'phone';
        if(strlen($search) >= 3) {
            if(strlen($search) > 60) {
                $search = mb_substr($search, 0, 60);
            }
            if(preg_match("/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/", $search, $matches)) {
                $search = $matches[0];
                $searchBy = 'email';
            } else {
                $search = str_replace([' ', '.'], '', $search);
                $search = preg_replace("/^\+\d{3}/", "", $search); //remove prefix
                $search = ltrim($search, '0');
                if(strlen($search) < 4) {
                    $doNotSearch = true;
                }
            }
//            $logger->error('recherche  :'.$search.' -- '.$doNotSearch);
            if(!$doNotSearch) {
                $users = $this->entityManager->getRepository(User::class)->searchedUsersForRelation($search, $searchBy);
                $statuses = ['pending', 'active'];
                $relationsList = $this->entityManager->getRepository(Relation::class)
                    ->findListOfRelations($currentUser, $statuses);
                $searchedRelationsListUI = $this->relationUI->getSearchedRelationsList($users, $currentUser, $relationsList);
            }
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'relations' => $searchedRelationsListUI,
            'searchBy' => $searchBy
        ]);
        return $jsonResponse;
    }

    #[Route('/add-relation', methods: ['POST'])]
    public function addRelation(Request $request, LoggerInterface $logger) {
        $currentUser =  $this->getUser();
        $thread = null;
        $jsonResponse = new JsonResponse();
        //ajout user
        if(!is_null($request->get('userAdd'))) {
            $userId = $request->get('userAdd');
            $userToAdd = $this->entityManager->getRepository(User::class)->findOneBy([
                'id' => $userId
            ]);
            if(is_null($userToAdd)) {
                throw new \ErrorException();
            }
            $thread = $this->relationService->defineRelation($currentUser, $userToAdd);
            $relation = $this->entityManager->getRepository(Relation::class)
                ->findRelation($currentUser, $userToAdd);
            if($relation->getStatus() != 'active') {
                $jsonResponse->setStatusCode(403);
                return $jsonResponse;
            }
        }
        $threadUI = null;
        if(!is_null($thread)) {
            $threadUI = $this->threadUI->getThreadUI($thread, $currentUser->getId());
        }
        $jsonResponse->setData([
            'thread' => $threadUI
        ]);
        return $jsonResponse;
    }

  
    #[Route('/validate-relation', methods: ['POST'])]
    public function validateRelation(Request $request) {
        $currentUser = $this->getUser();
        //ajout user
        $accept = $request->get('accept') == 'true';
        $relation = $this->entityManager->getRepository(Relation::class)->findOneBy([
            'id' => $request->get('relation'),
            'userTarget' => $currentUser,
            'status' => 'pending'
        ]);
        if(!is_null($relation)) {
            if($accept) {
                $relation->setStatus('active');
                $this->entityManager->persist($relation);
                $this->entityManager->flush();
            } else {
                $this->relationService->blockRelation($relation, $currentUser);
            }
        } else {
            throw new \ErrorException('Error relation '.$request->get('relation'));
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([]);
        return $jsonResponse;
    }


   
    #[Route('/relation-block', methods: ['POST'])]
    public function blockRelationRelation(Request $request) {
        $currentUser = $this->getUser();
        $relationId = $request->get('relationId');
        /** @var Relation $relation */
        $relation = $this->entityManager->getRepository(Relation::class)->findOneBy([
            'id' => $relationId
        ]);
        if($relation->getUserSource()->getId() == $currentUser->getId() ||
            $relation->getUserTarget()->getId() == $currentUser->getId()) {
            $this->relationService->blockRelation($relation, $currentUser);
            //delete thread
            /** @var ChatThread $thread */
            $thread = $this->entityManager->getRepository(ChatThread::class)->findThreadSimpleForUsers($relation->getUserTarget(), $relation->getUserSource());
            if(!is_null($thread)) {
                $this->entityManager->getRepository(ChatNotification::class)->deleteNotificationOfThreadForUser($thread, $currentUser);
                $cacheKey = $this->cacheService->chatNbNotificationKey($relation->getUserTarget());
                $cacheKey2 = $this->cacheService->chatNbNotificationKey($relation->getUserSource());
                $this->cacheApp->deleteItem($cacheKey);
                $this->cacheApp->deleteItem($cacheKey2);
                $thread->setDeletedAt(new \DateTime());
                $this->entityManager->persist($thread);
                $this->entityManager->flush();
            }
        }
        return new JsonResponse();
    }

    #[Route('/send-invitation', methods: ['POST'])]
    public function searchInvitation(Request $request) {
        $currentUser = $this->getUser();
        $email = trim(strtolower($request->get('email')));
        $isSend = false;
        if(strlen($email) >= 3) {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $users = $this->entityManager->getRepository(User::class)->searchedUsersForRelation($email, 'email');
                if(empty($users)) {
                    $invitationExist = $this->entityManager->getRepository(Invitation::class)->findExisting($currentUser, $email);
                    if(is_null($invitationExist)) {
                        $invitation = new Invitation();
                        $invitation->setCreatedBy($currentUser);
                        $invitation->setEmail($email);
                        $this->entityManager->persist($invitation);
                        $this->entityManager->flush();
                        $this->notificationService->notifInvitation($invitation);
                        $isSend = true;
                    }
                }
            }
        }
        $jsonResponse = new JsonResponse();
        if($isSend) {
            $jsonResponse->setData([
                'message' => 'Votre invitation est envoyé et votre contact sera ajouté à la création de son compte, merci !'
            ]);
        } else {
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData([
                'message' => 'Email invalide ou demande déjà envoyée !'
            ]);
        }
        return $jsonResponse;
    }

}
