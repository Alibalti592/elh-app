<?php

namespace App\Repository;

use App\Entity\MosqueNotifDece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MosqueNotifDece>
 *
 * @method MosqueNotifDece|null find($id, $lockMode = null, $lockVersion = null)
 * @method MosqueNotifDece|null findOneBy(array $criteria, array $orderBy = null)
 * @method MosqueNotifDece[]    findAll()
 * @method MosqueNotifDece[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MosqueNotifDeceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MosqueNotifDece::class);
    }

    public function finExistingMosqueNotifications($mosqueIds, $dece) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.dece = :dece')
            ->join('a.mosque', 'mosque')
            ->andWhere('mosque.id in (:mosqueIds)')
            ->setParameters(['mosqueIds' => $mosqueIds, 'dece'  => $dece]);
        return $qb->getQuery()->getResult();
    }

    public function findExistingDeceForMosque($mosque) {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->andWhere('a.mosque = :mosque')
            ->setParameters([
                'mosque' => $mosque
            ]);
        return $qb->getQuery()->getResult();
    }
}
