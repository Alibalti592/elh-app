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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotifToSend::class);
    }

    public function findNotifToSend() {
        $this->deleteToOldNotifs();
        $qb = $this->createQueryBuilder('a');
        $now = new \DateTime('now');
        $now->setTimezone(new \DateTimeZone('Europe/Paris'));
        $now->modify('+18 minutes');
        $qb
            ->andWhere('a.sendAt <= :now')
            ->setMaxResults(400)
            ->setParameters([
                'now' => $now
            ]);
        return $qb->getQuery()->getResult();
    }

    public function findPrayNotifOfUser($user) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.user = :user')
            ->andWhere('a.type IN (:types)')
            ->setParameters([
                'user' => $user,
                'types' => ["fajr","asr","chorouq","dohr","icha","maghreb"]
            ]);
        return $qb->getQuery()->getResult();
    }

    public function deleteToOldNotifs() {
        $qb = $this->createQueryBuilder('a');
        $now = new \DateTime('now');
        $now->setTimezone(new \DateTimeZone('Europe/Paris'));
        $now->modify('-35 minutes');
        $qb ->delete()
            ->andWhere('a.sendAt < :now')
            ->setParameters([
                'now' => $now
            ]);
        return $qb->getQuery()->getResult();
    }
}
