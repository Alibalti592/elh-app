<?php

namespace App\Repository;

use App\Entity\Salat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salat>
 *
 * @method Salat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salat[]    findAll()
 * @method Salat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalatRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salat::class);
    }

    public function getSalatsInMosques($mosqueIds, $excludeSalatIds)
    {
        $qb = $this->createQueryBuilder('a');
        $parameters = [
            'mosqueIds' => $mosqueIds,
            'today' => new \DateTime('today')
        ];
        $qb
            ->andWhere('mosque.id IN (:mosqueIds)')
            ->join('a.mosque', 'mosque')
            ->andWhere('a.ceremonyAt >= :today')
            ->orderBy('a.ceremonyAt', 'DESC');
        if(!empty($excludeSalatIds)){
            $qb->andWhere('a.id NOT IN (:excludeSalatIds)');
            $parameters['excludeSalatIds'] = $excludeSalatIds;
        }
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

    public function getSalatsOfuser($user, $passedOnly = false)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.createdBy = :user')
            ->orderBy('a.ceremonyAt', 'DESC');
        $parameters = [
            'user' => $user
        ];
        if($passedOnly) {
            $qb->andWhere('a.ceremonyAt >= :today');
            $parameters['today'] = new \DateTime('today');
        }
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

    public function getSalatsOfUserComming($user)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.createdBy = :user')
            ->andWhere('a.ceremonyAt >= :today')
            ->orderBy('a.ceremonyAt', 'DESC')
            ->setParameters([
                'user' => $user,
                'today' => new \DateTime('today')
            ]);
        return $qb->getQuery()->getResult();
    }

    public function existingSalats($ceremonyAt, $mosque)
    {
        //same day
        $from = new \DateTime($ceremonyAt->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($ceremonyAt->format("Y-m-d")." 23:59:59");
        $qb = $this->createQueryBuilder("a");
        $parameters = [
            'from' => $from,
            'to' => $to,
        ];
        $qb->andWhere('a.ceremonyAt BETWEEN :from AND :to');
        if(!is_null($mosque)) {
            $qb->andWhere('a.mosque = :mosque');
            $parameters['mosque'] = $mosque;
        }
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }

}
