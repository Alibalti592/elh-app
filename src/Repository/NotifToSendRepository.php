<?php

namespace App\Repository;

use App\Entity\NotifToSend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotifToSend>
 *
 * @method NotifToSend|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotifToSend|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotifToSend[]    findAll()
 * @method NotifToSend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotifToSendRepository extends ServiceEntityRepository
{
    public const PRAY_TYPES = ["fajr","asr","chorouq","dohr","icha","maghreb"];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotifToSend::class);
    }

   public function findNotifToSend() {
    $this->deleteToOldNotifs();

    $qb = $this->createQueryBuilder('a');
    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone('Europe/Paris'));

    $qb
        ->andWhere('a.sendAt <= :now')
        ->andWhere('a.status = :status')
        ->setMaxResults(400)
        ->setParameters([
            'now' => $now,
            'status' => 'pending',
        ]);

    return $qb->getQuery()->getResult();
}


    public function findPrayNotifOfUser($user) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.user = :user')
            ->andWhere('a.view = :view')
            ->andWhere('a.type IN (:types)')
            ->setParameters([
                'user' => $user,
                'view' => 'pray',
                'types' => self::PRAY_TYPES
            ]);
        return $qb->getQuery()->getResult();
    }

    public function deletePrayNotifOfUser($user): int
    {
        return $this->createQueryBuilder('a')
            ->delete()
            ->andWhere('a.user = :user')
            ->andWhere('a.view = :view')
            ->andWhere('a.type IN (:types)')
            ->setParameter('user', $user)
            ->setParameter('view', 'pray')
            ->setParameter('types', self::PRAY_TYPES)
            ->getQuery()
            ->execute();
    }

   public function deleteToOldNotifs() {
    $qb = $this->createQueryBuilder('a');
    $now = new \DateTime('now');
    $now->setTimezone(new \DateTimeZone('Europe/Paris'));
    $now->modify('-35 minutes');

    $qb->delete()
        ->andWhere('a.sendAt < :now')
        ->andWhere('(a.isRead IS NULL OR a.isRead = :one)')
        ->setParameters([
            'now' => $now,
            'one' => 1,
        ]);

    return $qb->getQuery()->getResult(); // kept same behavior as your code
}
}
