<?php
namespace App\Services;

use App\Entity\Relation;
use App\Services\Chat\ThreadService;
use App\Services\NotificationService;
use App\UIBuilder\UserUI;
use Doctrine\ORM\EntityManagerInterface;

class RelationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, NotificationService $notificationService, UserUI $userUI, private readonly ThreadService $threadService)
    {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
        $this->userUI = $userUI;
    }

    /**
     * Active relation depdning on status
     * @param $currentUser
     * @param $userToAdd
     */
    public function defineRelation($currentUser, $userToAdd, $defaultStatus = 'pending')
    {
        $thread = null;
        $sendNewRelationNotf = false;
        if (!is_null($userToAdd) && $currentUser->getId() != $userToAdd->getId()) {
            /** @var Relation $relation */
            $relation = $this->entityManager->getRepository(Relation::class)
                ->findRelation($currentUser, $userToAdd);
            if (is_null($relation)) {
                $relation = new Relation();
                $defaultStatus = 'active';
                $relation->setStatus($defaultStatus);
                $relation->setUserSource($currentUser);
                $relation->setUserTarget($userToAdd);
                $sendNewRelationNotf = true;
            } elseif (
                ($relation->getStatus() == 'blocked_by_source' && $relation->getUserSource()->getId() == $currentUser->getId()) ||
                ($relation->getStatus() == 'blocked_by_target' && $relation->getUserTarget()->getId() == $currentUser->getId())) {
                $relation->setStatus('active');
            } elseif ($relation->getStatus() == 'blocked_by_both') {
                if ($relation->getUserSource()->getId() == $currentUser->getId()) {
                    $relation->setStatus('blocked_by_target');
                } else {
                    $relation->setStatus('blocked_by_source');
                }
            } elseif ($relation->getStatus() == 'pending' && $relation->getUserTarget()->getId() == $currentUser->getId()) {
                $relation->setStatus('active');
            }
            $this->entityManager->persist($relation);
            $this->entityManager->flush();
            $thread = $this->threadService->getSimpleThreadFoUsers($currentUser, $userToAdd);
        }
        if($sendNewRelationNotf && !is_null($thread)) {
            //send notif userToAdd
            $userUi  = $this->userUI->getUserProfilUI($currentUser);
            $this->notificationService->notifForNewRelation($currentUser, $userToAdd, $userUi, $thread);
        }
        return $thread;
    }

    public function blockRelation(Relation $relation, $currentUser)
    {
        if (!is_null($relation)) {
            if ($relation->getUserSource()->getId() == $currentUser->getId()) {
                if ($relation->getStatus() == 'blocked_by_target') {
                    $relation->setStatus('blocked_by_both');
                } else {
                    $relation->setStatus('blocked_by_source');
                }
            } else {
                if ($relation->getStatus() == 'blocked_by_source') {
                    $relation->setStatus('blocked_by_both');
                } else {
                    $relation->setStatus('blocked_by_target');
                }
            }
            $this->entityManager->persist($relation);
            $this->entityManager->flush();
        }
    }
}