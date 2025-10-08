<?php

namespace App\Repository;

use App\Entity\Dece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dece>
 *
 * @method Dece|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dece|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dece[]    findAll()
 * @method Dece[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dece::class);
    }

    public function getDecesOfuser($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.createdBy = :user')
            ->orderBy('a.date', 'DESC')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }
}
