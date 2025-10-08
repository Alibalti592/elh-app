<?php

namespace App\Repository;

use App\Entity\Pompe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pompe>
 *
 * @method Pompe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pompe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pompe[]    findAll()
 * @method Pompe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PompeRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pompe::class);
    }

    public function findManagedPompe($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.managedBy =:user')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }

    public function countManagedPompes($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('COUNT(a)')
            ->andWhere('a.managedBy =:user')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findPompesByDistance($lat, $lng, $distance, $hasOwner = false) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('a as pompe' )
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
        if($hasOwner) {
            $qb->andWhere('a.managedBy IS NOT NULL');
        }
        $qb->andWhere('a.online = 1');
        $qb->andWhere('a.validated = 1');
        return $qb->getQuery()->getResult();
    }

}
