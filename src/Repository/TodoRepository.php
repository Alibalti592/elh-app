<?php

namespace App\Repository;

use App\Entity\Todo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository
 *
 * @method null find($id, $lockMode = null, $lockVersion = null)
 * @method null findOneBy(array $criteria, array $orderBy = null)
 * @method Todo[]    findAll()
 * @method Todo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TodoRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Todo::class);
    }

    public function findAllOrdered()
    {
        $qb = $this->createQueryBuilder('a');
        $qb ->orderBy('a.ordered', 'ASC')
            ->setMaxResults(500);
        return $qb->getQuery()->getResult();

    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findMaxOrdered()
    {
       $qb = $this->createQueryBuilder('a');
       $qb->select('MAX(a.ordered)');
       try {
           return $qb->getQuery()->getSingleScalarResult();
       } catch (NoResultException $e) {
           return 0;
       }

    }

}
