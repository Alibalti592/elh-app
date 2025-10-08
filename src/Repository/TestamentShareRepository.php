<?php

namespace App\Repository;

use App\Entity\TestamentShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TestamentShare>
 *
 * @method TestamentShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestamentShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestamentShare[]    findAll()
 * @method TestamentShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestamentShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestamentShare::class);
    }


    public function findSharedTestaments($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('testament')
            ->join('a.testament', 'testament')
            ->andWhere('a.user = :user')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $testament
     * @param $user
     * @return float|int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findExistingShare($testament, $user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.testament = :testament')
            ->andWhere('a.user = :user')
            ->setMaxResults(1)
            ->setParameters([
                'testament' => $testament,
                'user' => $user
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findShareUserIds($testament)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('user.id as userId')
            ->leftJoin('a.user', 'user')
            ->andWhere('a.testament = :testament')
            ->setParameters([
                'testament' => $testament
            ]);
        $res = $qb->getQuery()->getResult();
        return array_column($res, 'userId');
    }


}
