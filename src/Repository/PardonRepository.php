<?php

namespace App\Repository;

use App\Entity\Pardon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pardon>
 *
 * @method Pardon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pardon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pardon[]    findAll()
 * @method Pardon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PardonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pardon::class);
    }

    public function getPardonsOfuser($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.createdBy = :user')
            ->orderBy('a.createdAt', 'DESC')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }

    public function getPardonsSharedWith($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb ->andWhere('pardonShares.shareWith = :user')
            ->leftJoin('a.pardonShares', 'pardonShares')
            ->orderBy('a.createdAt', 'DESC')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }
}
