<?php

namespace App\Repository;

use App\Entity\UserFeedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserFeedback>
 *
 * @method UserFeedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFeedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFeedback[]    findAll()
 * @method UserFeedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFeedback::class);
    }

    /**
     * @return UserFeedback[]
     */
    public function findRecent(int $limit = 500): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.user', 'u')
            ->addSelect('u')
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
