<?php

namespace App\Repository;

use App\Entity\Jeun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Jeun>
 *
 * @method Jeun|null find($id, $lockMode = null, $lockVersion = null)
 * @method Jeun|null findOneBy(array $criteria, array $orderBy = null)
 * @method Jeun[]    findAll()
 * @method Jeun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JeunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jeun::class);
    }

//    /**
//     * @return Jeun[] Returns an array of Jeun objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('j.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Jeun
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
