<?php

namespace App\Repository;

use App\Entity\Imam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Imam>
 *
 * @method Imam|null find($id, $lockMode = null, $lockVersion = null)
 * @method Imam|null findOneBy(array $criteria, array $orderBy = null)
 * @method Imam[]    findAll()
 * @method Imam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImamRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Imam::class);
    }

    public function findImamsByDistance($lat, $lng, $distance) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('a as imam' )
            ->addSelect(
                '( 3959 * acos( least(1.0, cos(radians(' . $lat . ')))' .
                '* cos( radians( location.lat ) )' .
                '* cos( radians( location.lng )' .
                '- radians(' . $lng . ') )' .
                '+ sin( radians(' . $lat . ') )' .
                '* sin( radians( location.lat ) ) ) ) as distance'
            )
            ->join('a.location', 'location')
            ->having('distance < :distance')
            ->setParameter('distance', $distance)
            ->orderBy('distance', 'ASC')
            ->setMaxResults(25);
        $qb->andWhere('a.online = 1');
        return $qb->getQuery()->getResult();
    }
}
