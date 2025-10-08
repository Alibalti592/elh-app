<?php

namespace App\Repository;

use App\Entity\PardonShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PardonShare>
 *
 * @method PardonShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method PardonShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method PardonShare[]    findAll()
 * @method PardonShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PardonShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PardonShare::class);
    }


    public function findUserIdsInShare($pardon)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('shareWith.id as userId')
            ->leftJoin('a.shareWith', 'shareWith')
            ->andWhere('a.pardon = :pardon')
            ->setParameters([
                'pardon' => $pardon
            ]);
        $res = $qb->getQuery()->getResult();
        return array_column($res, 'userId');
    }
}
