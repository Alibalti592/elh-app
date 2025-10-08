<?php

namespace App\Repository;

use App\Entity\ChatNotification;
use App\Entity\ChatThread;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatNotification[]    findAll()
 * @method ChatNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatNotification::class);
    }

    public function findNotificationsThreadIdsForUser($threads, User $user) {
        $qb = $this->createQueryBuilder('a', 'a.user');
        $qb ->select('thread.id')
            ->join('a.thread', 'thread')
            ->andWhere('a.thread IN (:threads)')
            ->andWhere('a.user = :user')
            ->setParameters( [
                'threads' => $threads,
                'user' => $user
            ]);
        $result =  $qb->getQuery()->getArrayResult();
        if(!empty($result)) {
            $result = array_column( $result, 'id');
        }
        return $result;
    }

    public function findNotificationsOfThread(ChatThread $thread) {
        $qb = $this->createQueryBuilder('a', 'a.user');
        $qb ->addSelect('user')
            ->andWhere('a.thread = :thread')
            ->leftJoin('a.user', 'user')
            ->setParameters( [
                'thread' => $thread
            ]);
        return $qb->getQuery()->getResult();
    }

    public function countNotificationsOfUser(User $user) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('COUNT(a.id)')
            ->andWhere('a.user = :user')
            ->setParameters( [
                'user' => $user
            ]);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function deleteNotificationOfThreadForUser(ChatThread $thread, $user) {
        $qb = $this->createQueryBuilder('a');
        $qb ->delete()
            ->andWhere('a.thread = :thread')
            ->andWhere('a.user = :user')
            ->setParameters( [
                'thread' => $thread,
                'user' => $user,
            ]);
        return $qb->getQuery()->execute();
    }

    public function deleteNotificationsOfThread(ChatThread $thread) {
        $qb = $this->createQueryBuilder('a');
        $qb ->delete()
            ->andWhere('a.thread = :thread')
            ->setParameters( [
                'thread' => $thread,
            ]);
        return $qb->getQuery()->execute();
    }
}
