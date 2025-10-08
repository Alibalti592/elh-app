<?php

namespace App\Repository;

use App\Entity\Relation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Relation>
 *
 * @method Relation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Relation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Relation[]    findAll()
 * @method Relation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Relation::class);
    }
    /**
     * @return Relation[] Returns an array of SocialRelation objects
     */
    public function findListOfRelations($user, $statuses, $limit = null, $search = null) {
        $qb = $this->createQueryBuilder('s')
            ->where('s.userSource = :user OR s.userTarget = :user')
            ->leftJoin('s.userSource', 'user1')
            ->leftJoin('s.userTarget', 'user2')
            ->andWhere('s.status IN (:statuses)')
            ->orderBy('s.id', 'DESC');
        $parameters = [
            'user' => $user,
            'statuses' => $statuses
        ];
        if(!is_null($limit)) {
            $qb->setMaxResults($limit);
        }
        if(!is_null($search)) {
            $qb->andWhere('user1.firstname LIKE :search OR user1.lastname LIKE :search OR 
            user2.firstname LIKE :search OR user2.lastname LIKE :search');
            $parameters['search'] = '%'.$search.'%';
        }
        $qb->setParameters($parameters);
        return  $qb->getQuery()->getResult();
    }

    /**
     * @return User[]
     */
    public function findListOfRelationUsers($user, $statuses, $limit = 4, $page = 1, $search = null) {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('user_source')
            ->addSelect('user_target')
            ->leftJoin('s.userSource', 'user_source')
            ->leftJoin('s.userTarget', 'user_target')
            ->where('s.userSource = :user OR s.userTarget = :user')
            ->andWhere('s.status IN (:statuses)')
            ->orderBy('s.id', 'DESC');
        $offset = ($page - 1)*$limit;
        $qb->setMaxResults($limit)
            ->setFirstResult($offset);
        $parameters = [
            'user' => $user,
            'statuses' => $statuses
        ];
        if(!is_null($search)) {
            if(str_contains($search, '@')) {
                $earchParts = explode('@', $search);
                if(isset($earchParts[0])) {
                    $search = '%'.$earchParts[0].'%';
                }
            }
            $qb
                ->andWhere('MATCH_AGAINST(user_source.firstname, user_source.lastname) AGAINST (:search boolean) > 0 OR
                MATCH_AGAINST(user_target.firstname, user_target.lastname) AGAINST (:search boolean) > 0');
            $parameters['search' ] = $search;
        }
        $qb ->setParameters($parameters);
        $results = $qb->getQuery()->getResult();
        $usersIndexById = [];
        /** @var SocialRelation $result */
        foreach ($results as $result) {
            if($result->getUserSource()->getId() != $user->getId()) {
                $usersIndexById[$result->getUserSource()->getId()] = $result->getUserSource();
            } else {
                $usersIndexById[$result->getUserTarget()->getId()] = $result->getUserTarget();
            }
        }

        return $usersIndexById;
    }

    public function findRelationsToValidate($user) {
        $qb = $this->createQueryBuilder('s')
            ->where('s.userTarget = :user')
            ->andWhere('s.status = :status')
            ->setParameters([
                'user' => $user,
                'status' => 'pending'
            ])
            ->orderBy('s.id', 'DESC');
        return  $qb->getQuery()->getResult();
    }


    public function countActiverRelations($user) {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.userSource = :user OR s.userTarget = :user')
            ->andWhere('s.status = :status')
            ->setParameters([
                'user' => $user,
                'status' => 'active'
            ]);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $user1
     * @param $user2
     * @return ?Relation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRelation($user1, $user2) {
        return $this->createQueryBuilder('s')
            ->where('s.userSource = :user1 AND s.userTarget = :user2')
            ->orWhere('s.userSource = :user2 AND s.userTarget = :user1')
            ->setParameters([
                'user1' => $user1,
                'user2' => $user2
            ])
            ->orderBy('s.id', 'desc')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }


    /**
     * @return ids[]
     */
    public function findListOfActiveRelationsUserIds($user, $limit = 2000, $page = 1) {
        $offset = ($page - 1)*$limit;
        $qb = $this->createQueryBuilder('s')
            ->select('userTarget.id AS userTargetId, userSource.id AS userSourceId')
            ->join('s.userTarget', 'userTarget')
            ->join('s.userSource', 'userSource')
            ->where('s.userSource = :user OR s.userTarget = :user')
            ->andWhere('s.status IN (:statuses)')
            ->setParameters([
                'user' => $user,
                'statuses' => ['active']
            ])
            ->orderBy('s.id', 'DESC');
        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);
        $results =  $qb->getQuery()->getResult();
        $ids = [];
        foreach ($results as $resultByIds) {
            $ids = array_unique(array_merge($ids, array_values($resultByIds)));
        }
        return $ids;
    }
}
