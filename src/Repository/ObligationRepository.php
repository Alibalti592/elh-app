<?php

namespace App\Repository;

use App\Entity\Obligation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Tranche;

/**
 * @extends ServiceEntityRepository<Obligation>
 *
 * @method Obligation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Obligation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Obligation[]    findAll()
 * @method Obligation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObligationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Obligation::class);
    }

    public function findObligationsOfUser($user, $type, $filter)
    {
        $qb = $this->createQueryBuilder('a');
        $params = [
            'type' => $type,
            'user' => $user,
        ];
        if($filter == 'processing') {
            $qb ->andWhere('a.status = :status OR a.status IS NULL OR a.status = :status2');
            $params['status'] = 'ini';
            $params['status2'] = 'processing';
        } else if($filter == 'refund') {
            $qb ->andWhere('a.status = :status');
            $params['status'] = 'refund';
        }
        if($type == 'jed') {
            $qb ->andWhere('(a.type = :type AND a.createdBy = :user) OR (a.type = :type2 AND a.relatedTo = :user)');
            $params['type2'] = 'onm';
        } else if($type == 'onm') {
            $qb ->andWhere('(a.type = :type AND a.createdBy = :user) OR (a.type = :type2 AND a.relatedTo = :user)');
            $params['type2'] = 'jed';
        } else {
            $qb ->andWhere('a.type = :type')
                ->andWhere('a.createdBy = :user OR a.relatedTo = :user');
        }
        $qb
            ->andWhere('a.deletedAt IS NULL')
            ->orderBy('a.dateStart', 'ASC')
            ->addOrderBy('a.date', 'ASC')
            ->setParameters($params);
        return $qb->getQuery()->getResult();
    }

/**
     * Get all tranches of an obligation
     */
    public function findTranchesOfObligation(Obligation $obligation)
    {
        return $this->getEntityManager()
            ->getRepository(Tranche::class)
            ->findBy(['obligation' => $obligation], ['paidAt' => 'ASC']);
    }

    /**
     * Get tranches that are still pending for a user
     */
    public function findPendingTranchesForUser($user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from(Tranche::class, 't')
            ->andWhere('t.emprunteur = :user')
            ->andWhere('t.status = :status')
            ->setParameters([
                'user' => $user,
                'status' => 'en attente',
            ])
            ->orderBy('t.paidAt', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Count pending tranches for a user
     */
    public function countPendingTranchesForUser($user): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(t.id)')
            ->from(Tranche::class, 't')
            ->andWhere('t.emprunteur = :user')
            ->andWhere('t.status = :status')
            ->setParameters([
                'user' => $user,
                'status' => 'en attente',
            ]);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function findObligationsShared($user, $type)
    {
        $qb = $this->createQueryBuilder('a');
        $qb ->andWhere('a.type = :type')
            ->andWhere('a.relatedTo = :user')
            ->andWhere('a.deletedAt IS NULL')
            ->setParameters([
                'type' => $type,
                'user' => $user,
            ]);
        return $qb->getQuery()->getResult();
    }

    public function findObligationCanRefund($user, $obligationId)
    {
        $qb = $this->createQueryBuilder('a');
        $qb ->andWhere('a.id = :obligationId')
            ->andWhere('a.relatedTo = :user OR a.createdBy = :user')
            ->andWhere('a.deletedAt IS NULL')
            ->setMaxResults(1)
            ->setParameters([
                'obligationId' => $obligationId,
                'user' => $user,
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findObligationToRefund($user, $type = null)
    {
        $qb = $this->createQueryBuilder('a');
        $parameters = [
            'user' => $user,
            'status' => 'refund',
            'now' => new \DateTime('now'),
        ];
        $qb
            ->andWhere('a.relatedTo = :user OR a.createdBy = :user')
            ->andWhere('a.status IS NULL OR a.status != :status')
            ->andWhere('a.deletedAt IS NULL')
            ->andWhere('a.dateStart IS NOT NULL AND a.dateStart <= :now');
        if(!is_null($type)) {
            $qb->andWhere('a.type = :type');
            $parameters['type'] = $type;
        }
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

    public function countObligationToRefund($user, $type)
    {
        $qb = $this->createQueryBuilder('a');
        $now = new \DateTime('today');
        $now->setTime(23,59,59);
        $parameters = [
            'user' => $user,
            'status' => 'refund',
            'now' => $now,
        ];
        $qb ->select('COUNT(a.id)')
            ->andWhere('a.status IS NULL OR a.status != :status')
            ->andWhere('a.deletedAt IS NULL')
            ->andWhere('a.dateStart IS NOT NULL AND a.dateStart <= :now');
        if($type == 'onm') {
            $qb->andWhere('(a.type = :type AND a.createdBy = :user) OR (a.type = :type2 AND a.relatedTo = :user)');
            $parameters['type'] = $type;
            $parameters['type2'] = "jed";
        } elseif($type == 'jed') {
            $qb->andWhere('(a.type = :type AND a.createdBy = :user) OR (a.type = :type2 AND a.relatedTo = :user)');
            $parameters['type'] = $type;
            $parameters['type2'] = "onm";
        } else { //amana
            $qb->andWhere('a.relatedTo = :user OR a.createdBy = :user');
            $qb->andWhere('a.type = :type');
            $parameters['type'] = $type;
        }
        $qb->setParameters($parameters);
        return $qb->getQuery()->getSingleScalarResult();
    }


    public function findObligationNotRefund($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.relatedTo = :user OR a.createdBy = :user')
            ->andWhere('a.status IS NULL OR a.status != :status')
            ->andWhere('a.deletedAt IS NULL')
            ->orderBy('a.date', 'DESC')
            ->setParameters([
                'user' => $user,
                'status' => 'refund',
            ]);
        return $qb->getQuery()->getResult();
    }


    public function findObligationsForNotif()
    {
        $qb = $this->createQueryBuilder('a');
        $startOfTomorrow = (new \DateTime('tomorrow'))->setTime(0, 0, 0);
        $endOfTomorrow = (new \DateTime('tomorrow'))->setTime(23, 59, 59);
        $parameters = [
            'status' => 'refund',
            'startOfTomorrow' => $startOfTomorrow,
            'endOfTomorrow' => $endOfTomorrow,
        ];
        $qb
            ->andWhere('a.status IS NULL OR a.status != :status')
            ->andWhere('a.deletedAt IS NULL')
            ->andWhere('a.dateStart IS NOT NULL AND a.dateStart BETWEEN :startOfTomorrow AND :endOfTomorrow');
        $qb->setParameters($parameters)
            ->setMaxResults(2000);

        return $qb->getQuery()->getResult();
    }


    public function hasObligationDoublon($user, $relatedTo, $date, $amount)
    {
        $qb = $this->createQueryBuilder('a');
        $startdate = clone $date;
        $enddate = clone $date;
        $startdate->setTime(0, 0, 0);
        $enddate->setTime(23, 59, 59);
        $parameters = [
            'status' => 'refund',
            'start' => $startdate,
            'end' => $enddate,
            'amount' => $amount,
            'user' => $user,
            'relatedTo' => $relatedTo,
        ];
        $qb
            ->andWhere('(a.relatedTo = :relatedTo AND a.createdBy = :user) OR (a.relatedTo = :user AND a.createdBy = :relatedTo)')
            ->andWhere('a.deletedAt IS NULL')
            ->andWhere('a.status IS NULL OR a.status != :status')
            ->andWhere('a.date IS NOT NULL AND a.date BETWEEN :start AND :end')
            ->andWhere('a.amount = :amount');
        $qb ->setParameters($parameters)
            ->setMaxResults(1);

        return !is_null($qb->getQuery()->getOneOrNullResult());
    }
}
