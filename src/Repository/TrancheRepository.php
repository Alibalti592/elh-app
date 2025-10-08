<?php

namespace App\Repository;

use App\Entity\Tranche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tranche>
 *
 * @method Tranche|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tranche|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tranche[]    findAll()
 * @method Tranche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrancheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tranche::class);
    }

    /**
     * Exemple de méthode pour récupérer les notifications à envoyer
     */
    public function findNotifToSend(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', 'en attente')
            ->getQuery()
            ->getResult();
    }

    /**
     * Ajouter ici d'autres méthodes custom si nécessaire
     */
}
