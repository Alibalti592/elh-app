<?php

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function findlastMessages($thread, $messageId) {
        $parameters = [
            'thread' => $thread
        ];
        $qb = $this->createQueryBuilder('a');
        $qb ->select('a as message')
            ->addSelect('user.id as userID')
            ->join('a.createdBy', 'user')
            ->andWhere('a.chatThread = :thread')
            ->orderBy("a.createdAt", 'DESC');
        if($messageId != null) {
            $qb->andWhere('a.id > :messageId');
            $parameters['messageId'] = $messageId;
        }
        $qb->setParameters($parameters);
        return new Paginator($qb->getQuery(), true);
    }

    public function findMessages($thread, $page, $limit) {
        $offset = ($page - 1)*$limit;
        $qb = $this->createQueryBuilder('a');
        $qb ->select('a as message')
            ->addSelect('user.id as userID')
            ->join('a.createdBy', 'user')
            ->andWhere('a.chatThread = :thread')
            ->orderBy("a.createdAt", 'DESC')
            ->setParameters([
                'thread' => $thread
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        return new Paginator($qb->getQuery(), true);
    }

    public function findLastMessageSendByUser($thread, $user) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.createdBy = :user')
            ->andWhere('a.chatThread = :thread')
            ->orderBy("a.createdAt", 'DESC')
            ->setParameters([
                'thread' => $thread,
                'user' => $user,
            ])
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }

    //check volume upload last 24h
    public function getVolumeUploadedForUser(User $user)
    {
        $qb = $this->createQueryBuilder('a');
        $startDay = new \DateTime('now');
        $startDay->modify('-24 hours');
        $qb ->select('SUM(file.fileSize) as volume')
            ->leftJoin('a.file', 'file')
            ->andWhere('file IS NOT NULL')
            ->andWhere('a.createdAt >= :startDay')
            ->andWhere('a.createdBy = :user')
            ->setParameters([
                'user' => $user,
                'startDay' => $startDay,
            ]);
        return floatval($qb->getQuery()->getSingleScalarResult());
    }

    public function deleteMessagesOfThread($thread) {
        $qb = $this->createQueryBuilder('a');
        $query = $qb->delete()
            ->where('a.chatThread = :thread')
            ->setParameters([
                'thread' => $thread
            ]);
        $query->getQuery()->execute();
    }
}
