<?php

namespace App\Repository;

use App\Entity\PrayNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrayNotification>
 *
 * @method PrayNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrayNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrayNotification[]    findAll()
 * @method PrayNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrayNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrayNotification::class);
    }


    public function findPrayNotifToAdd(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.notifAdded = 0')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult()
        ;
    }

}
