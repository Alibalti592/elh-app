<?php

namespace App\Repository;

use App\Entity\Deuil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deuil>
 *
 * @method Deuil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deuil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deuil[]    findAll()
 * @method Deuil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeuilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deuil::class);
    }

    public function loadAllDeuils()
    {
        $qb = $this->createQueryBuilder('a');
        return $qb->getQuery()->getResult();
    }

    public function loadDeuil($type)
    {
        $qb = $this->createQueryBuilder('a');
        $qb ->andWhere('a.type = :type')
            ->setParameters([
                'type' => $type
            ])
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
