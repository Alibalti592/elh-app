<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use _BaseRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findNbRegistrationForPeriod($startDate, $endDate) {
        $qb = $this->createQueryBuilder('a');
        $parameters = [
            'starDate' => $startDate,
            'endDate' => $endDate
        ];
        $qb ->select('COUNT(a.id)')
            ->andWhere('a.createAt >= :starDate')
            ->andWhere('a.createAt <= :endDate')
            ->andWhere('a.deletedAt IS NULL');
        $qb ->setParameters($parameters);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countUsers($search = false) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('COUNT(DISTINCT a.id)')
            ->andWhere('a.deletedAt IS NULL');
        $this->setSearchOnQb($qb, $search);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countUsersBetweenDates($dateStart, $dateEnd) {
        $qb = $this->createQueryBuilder('a');
        $qb ->select('COUNT(DISTINCT a.id)');
        $qb ->andWhere('a.createAt <= :end')
            ->andWhere('a.createAt >= :start')
            ->andWhere('a.deletedAt IS NULL')
        ;
        $qb->setParameters([
            'start' => $dateStart,
            'end' => $dateEnd,
        ]);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findPaginatedUsers($offset, $nbPerPage, $search) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->setFirstResult($offset)
            ->setMaxResults($nbPerPage)
            ->orderBy('a.id', 'ASC');
        $this->setSearchOnQb($qb, $search);
        return $qb->getQuery()->getResult();
    }

    public function setSearchOnQb($qb, $search) {
        if($search) {
            $search = $this->stripAccents($search);
           if(str_contains($search, '@')) {
               $earchParts = explode('@', $search);
               if(isset($earchParts[0])) {
                   $search = '%'.$earchParts[0].'%';
               }
           }
            $qb
                ->where('MATCH_AGAINST(a.firstname, a.lastname) AGAINST (:search boolean)>0 ')
                ->orWhere('a.email LIKE :search')
                ->setParameters([
                    'search' => $search
                ]);
        }
    }

    public function stripAccents($str) {
        return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public function searchedUsersForRelation($search, $searchBy = 'email') {
        $qb = $this->createQueryBuilder('a');
        if($searchBy == 'email') {
            $qb ->where('a.email LIKE :search');

        } else { //phone
            $search = "%".$search;
            $qb->where('a.phone LIKE :search');
        }
        $qb ->setParameters([
            'search' => $search
        ]);
        $qb->setMaxResults(20);
        return $qb->getQuery()->getResult();
    }

    public function findUsersById($userIds)
    {
        $qb = $this->createQueryBuilder('a');
        //use setSearchOnQb if needed name ..
        $qb ->where('a.id in (:userIds)')
            ->setParameters([
                'userIds' => $userIds
            ]);
        return $qb->getQuery()->getResult();
    }


    /*********************** ADMIN **********************/

    public function findListFiltered($crudParameters, $searchableFields = null) {
        $qb = $this->createQueryBuilder('a');
        $this->setBaseCrudParameters($crudParameters, $qb);
        $qb->andWhere('a.deletedAt IS NULL');
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }


    public function countListFiltered($crudParameters, $searchableFields = null) {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.deletedAt IS NULL');
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->setParameters($parameters);
        return $qb->getQuery()->getSingleScalarResult();
    }
}
