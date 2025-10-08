<?php

namespace App\Repository;

use App\Entity\Carte;
use App\Entity\CarteShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carte>
 *
 * @method Carte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Carte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Carte[]    findAll()
 * @method Carte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carte::class);
    }

    public function getCartesOfuser($user, $filter)
    {
        $params = [
            'user' => $user
        ];
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.createdBy = :user')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(100);

        if($filter == 'create') {
            $qb->leftJoin(CarteShare::class, 'cs', 'WITH', 'cs.carte = a.id')
                ->andWhere('cs.id IS NULL');
        } elseif($filter == 'send') {
            $qb->leftJoin(CarteShare::class, 'cs', 'WITH', 'cs.carte = a.id')
                ->andWhere('cs.id IS NOT NULL');
        }
        $qb->andWhere('(a.type = :salat AND a.deathDate >= :today) OR a.type != :salat');
        $params['salat'] = 'salat';
        $params['today'] = new \DateTime('today');
        $qb->setParameters($params);
        return $qb->getQuery()->getResult();
    }
}
