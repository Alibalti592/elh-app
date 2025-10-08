<?php

namespace App\Repository;

use App\Entity\ChatParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatParticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatParticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatParticipant[]    findAll()
 * @method ChatParticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatParticipant::class);
    }


    public function findParticipantUserIds($thread) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('user.id')
            ->leftJoin('a.user', 'user')
            ->andWhere('a.thread = :thread')
            ->setParameters([
                'thread' => $thread
            ]);
        return array_column($qb->getQuery()->getResult(),'id');
    }

    public function findParticipants($thread) {
        $qb = $this->createQueryBuilder('a');
        $qb ->leftJoin('a.user', 'user')
            ->andWhere('a.thread = :thread')
            ->setParameters([
                'thread' => $thread
            ]);
        return $qb->getQuery()->getResult();
    }



    public function findParticipant($user, $thread) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.thread = :thread')
            ->andWhere('a.user = :user')
            ->setMaxResults(1)
            ->setParameters([
                'thread' => $thread,
                'user' => $user,
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
