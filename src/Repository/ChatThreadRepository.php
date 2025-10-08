<?php

namespace App\Repository;

use App\Entity\ChatThread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatThread|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatThread|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatThread[]    findAll()
 * @method ChatThread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatThread::class);
    }


    public function findThreadSimpleForUsers($user1, $user2) {
        $qb = $this->createQueryBuilder('a')
            ->join('a.participants', 'p1')
            ->join('a.participants', 'p2')
            ->andWhere('a.type = :type')
            ->andWhere('p1.user = :user1 AND p2.user = :user2')
            ->setParameters([
                'type' => 'simple',
                'user1' => $user1,
                'user2' => $user2,
            ])
            ->setMaxResults(1);
           return $qb->getQuery()->getOneOrNullResult();
    }

    public function findThreadsOfUser($user, $offset, $maxResults) {
        $qb = $this->createQueryBuilder('a')
            ->distinct('a.id')
            ->where('a.createdBy = :user AND a.deletedAt IS NULL')
            ->orWhere('participants.user = :user AND a.deletedAt IS NULL')
            ->join('a.participants', 'participants')
            ->setParameter('user', $user)
            ->setFirstResult($offset)
            ->setMaxResults($maxResults)
            ->orderBy('a.lastUpdate', 'DESC');
        //return $qb->getQuery()->getResult();
        return new Paginator($qb->getQuery(), true);
    }

    public function findThread($threadId) {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->setParameter('id', $threadId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
