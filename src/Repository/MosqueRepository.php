<?php

namespace App\Repository;

use App\Entity\Location;
use App\Entity\Mosque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mosque>
 *
 * @method Mosque|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mosque|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mosque[]    findAll()
 * @method Mosque[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MosqueRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mosque::class);
    }

    public function findMosquesByDistance($lat, $lng, $distance, $hasOwner = false) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('a as mosque' )
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
        if($hasOwner) {
            $qb->andWhere('a.managedBy IS NOT NULL');
        }
        return $qb->getQuery()->getResult();
    }

    public function findMyMosquesGestion($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.managedBy = :user')
            ->setParameters([
                'user' => $user
            ]);
        return $qb->getQuery()->getResult();
    }


    public function findListFilteredAdmin($crudParameters, $searchableFields = null) {
        $qb = $this->createQueryBuilder('a');
        $this->setBaseCrudParameters($crudParameters, $qb);
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->join('a.location', 'location')
            ->orderBy('location.city', 'ASC');

        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

}
