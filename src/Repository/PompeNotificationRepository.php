<?php

namespace App\Repository;

use App\Entity\PompeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PompeNotification>
 *
 * @method PompeNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method PompeNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method PompeNotification[]    findAll()
 * @method PompeNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PompeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PompeNotification::class);
    }

    public function finExistingPompeNotifications($pompeIds, $dece) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.dece = :dece')
            ->join('a.pompe', 'pompe')
            ->andWhere('pompe.id in (:pompeIds)')
            ->setParameters(['pompeIds' => $pompeIds, 'dece'  => $dece]);
        return $qb->getQuery()->getResult();
    }

    public function findDemandForPompes($pompeIds) {
        $qb = $this->createQueryBuilder('a');
        $date = new \DateTime("-1 month");
        $qb
            ->andWhere('a.pompe IN (:pompeIds)')
            ->andWhere('a.status != :rejected OR a.createdAt >= :date')
            ->setParameters([
                'pompeIds' => $pompeIds,
                'rejected' => 'rejected',
                'date' => $date
            ]);
        return $qb->getQuery()->getResult();
    }

    public function findDemandsForDece($dece) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.dece = :dece')
            ->setParameters(['dece'  => $dece]);
        return $qb->getQuery()->getResult();
    }

    public function countForDece($dece, $status) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('COUNT(DISTINCT a.id)')
            ->andWhere('a.dece = :dece')
            ->andWhere('a.status = :status')
            ->setParameters(['status' => $status, 'dece'  => $dece]);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function updateStatusForDece($dece, $oldStatus, $newStatus) {
        $qb = $this->createQueryBuilder('a');
        $qb ->update()
            ->set('a.status', ':newStatus')
            ->andWhere('a.dece = :dece')
            ->andWhere('a.status = :oldStatus')
            ->setParameters(['oldStatus' => $oldStatus, 'newStatus' => $newStatus, 'dece'  => $dece]);
        return $qb->getQuery()->execute();
    }
}
