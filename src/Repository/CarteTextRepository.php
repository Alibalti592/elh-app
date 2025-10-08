<?php

namespace App\Repository;

use App\Entity\CarteText;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarteText>
 *
 * @method CarteText|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarteText|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarteText[]    findAll()
 * @method CarteText[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarteTextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarteText::class);
    }

    public function findTextOfCard($toOther, $type)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.forOther = :forOther')
            ->andWhere('a.type = :type')
            ->setMaxResults(1)
            ->setParameters([
                'forOther' => $toOther,
                'type' => $type,
            ]);
        return $qb->getQuery()->getOneOrNullResult();
        
    }
}
