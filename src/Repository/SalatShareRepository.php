<?php

namespace App\Repository;

use App\Entity\SalatShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SalatShare>
 *
 * @method SalatShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalatShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalatShare[]    findAll()
 * @method SalatShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalatShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalatShare::class);
    }

    public function getSalatsSharedOfuser($user, $passedOnly = false)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->join('a.salat', 'salat')
            ->andWhere('a.user = :user')
            ->orderBy('salat.ceremonyAt', 'DESC');
        $parameters = [
            'user' => $user
        ];
        if($passedOnly) {
            $qb->andWhere('salat.ceremonyAt >= :today');
            $parameters['today'] = new \DateTime('today');
        }
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

    public function findShareUserIds($salat)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('user.id as userId')
            ->leftJoin('a.user', 'user')
            ->andWhere('a.salat = :salat')
            ->setParameters([
                'salat' => $salat
            ]);
        $res = $qb->getQuery()->getResult();
        return array_column($res, 'userId');
    }
}
