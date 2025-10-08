<?php

namespace App\Repository;

use App\Entity\CarteShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarteShare>
 *
 * @method CarteShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarteShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarteShare[]    findAll()
 * @method CarteShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarteShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarteShare::class);
    }

    public function findSharedCartes($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('carte')
            ->join('a.carte', 'carte')
            ->andWhere('a.user = :user')
            ->orderBy('a.id', 'DESC')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }

    public function countSharedCartes($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('COUNT(DISTINCT(a.id))')
//            ->join('a.carte', 'carte')
            ->andWhere('a.user = :user')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findShareUserIds($carte)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('user.id as userId')
            ->leftJoin('a.user', 'user')
            ->andWhere('a.carte = :carte')
            ->setParameters([
                'carte' => $carte
            ]);
        $res = $qb->getQuery()->getResult();
        return array_column($res, 'userId');
    }

    public function removeShareds($carte)
    {
        $qb = $this->createQueryBuilder('a');
        $query = $qb->delete()
            ->where('a.carte = :carte')
            ->andWhere('a.carte = :carte')
            ->setParameters([
                'carte' => $carte
            ]);
        $query->getQuery()->execute();
    }
}
