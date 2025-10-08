<?php

namespace App\Repository;

use App\Entity\MosqueFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MosqueFavorite>
 *
 * @method MosqueFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method MosqueFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method MosqueFavorite[]    findAll()
 * @method MosqueFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MosqueFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MosqueFavorite::class);
    }

    public function findMosqueFavorited($user)
    {
        return$this->createQueryBuilder('a')
            ->addSelect('mosque')
            ->join('a.mosque', 'mosque')
            ->andWhere('a.user = :user')
            ->setParameters([
                'user' => $user,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findMosqueFavoriteIds($user)
    {
        $res = $this->createQueryBuilder('a')
            ->select('mosque.id')
            ->join('a.mosque', 'mosque')
            ->andWhere('a.user = :user')
            ->setParameters([
                'user' => $user,
            ])
            ->getQuery()
            ->getArrayResult();
        return array_column($res, 'id');
    }

    /**
     * @return MosqueFavorite
     */
    public function findMosqueIsFavorite($user, $mosque)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.mosque = :mosque')
            ->setParameters([
                'user' => $user,
                'mosque' => $mosque,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
