<?php

namespace App\Repository;

use App\Entity\Resetpassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resetpassword>
 *
 * @method Resetpassword|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resetpassword|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resetpassword[]    findAll()
 * @method Resetpassword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetpasswordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resetpassword::class);
    }

//    /**
//     * @return Resetpassword[] Returns an array of Resetpassword objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Resetpassword
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
