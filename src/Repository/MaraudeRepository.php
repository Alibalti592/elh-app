<?php

namespace App\Repository;

use App\Entity\Maraude;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Maraude>
 *
 * @method Maraude|null find($id, $lockMode = null, $lockVersion = null)
 * @method Maraude|null findOneBy(array $criteria, array $orderBy = null)
 * @method Maraude[]    findAll()
 * @method Maraude[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaraudeRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maraude::class);
    }

    public function findMaraudesByDistance($lat, $lng, $distance) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('a as maraude' )
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
            ->addOrderBy('a.date', 'ASC')
            ->setMaxResults(25);
        $qb->andWhere('a.online = 1');
        $qb->andWhere('a.validated = 1');
        return $qb->getQuery()->getResult();
    }

    public function findMyMaraudes($user) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->setParameter('user', $user)
            ->orderBy('a.date', 'ASC')
            ->setMaxResults(25);
        $qb->andWhere('a.managedBy = :user');
        return $qb->getQuery()->getResult();
    }
}
