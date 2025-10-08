<?php

namespace App\Repository;

use App\Entity\DeuilDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeuilDate>
 *
 * @method DeuilDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeuilDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeuilDate[]    findAll()
 * @method DeuilDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeuilDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeuilDate::class);
    }


    public function findNextDeuilDates($currentUser) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.user = :user')
            ->andWhere('a.endDate >= :date')
            ->orderBy('a.endDate', 'ASC')
            ->setParameters([
                'user' => $currentUser,
                'date' => new \DateTime('now')
            ]);
        return $qb->getQuery()->getResult();
    }
}
