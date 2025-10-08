<?php

namespace App\Repository;

use App\Entity\Intro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intro>
 *
 * @method Intro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intro[]    findAll()
 * @method Intro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IntroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intro::class);
    }

    public function loadIntro($page = null)
    {
        $qb = $this->createQueryBuilder('a');
        if(is_null($page)) {
            $qb->andWhere('a.page IS NULL');
        } else {
            $qb->andWhere('a.page = :page');
            $qb->setParameters([
                'page' => $page
            ]);
        }
        $qb->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
