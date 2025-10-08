<?php

namespace App\Repository;

use App\Entity\NavPageContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NavPageContent>
 *
 * @method NavPageContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method NavPageContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method NavPageContent[]    findAll()
 * @method NavPageContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NavPageContentRepository extends ServiceEntityRepository
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NavPageContent::class);
    }

    public function findListFiltered($crudParameters, $searchableFields = null) {
        $qb = $this->createQueryBuilder('a');
        $this->setBaseCrudParameters($crudParameters, $qb);
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }


    public function countListFiltered($crudParameters, $searchableFields = null) {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)');
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->setParameters($parameters);
        return $qb->getQuery()->getSingleScalarResult();
    }
}
