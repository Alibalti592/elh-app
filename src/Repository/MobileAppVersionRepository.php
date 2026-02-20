<?php

namespace App\Repository;

use App\Entity\MobileAppVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MobileAppVersion>
 *
 * @method MobileAppVersion|null find($id, $lockMode = null, $lockVersion = null)
 * @method MobileAppVersion|null findOneBy(array $criteria, array $orderBy = null)
 * @method MobileAppVersion[]    findAll()
 * @method MobileAppVersion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MobileAppVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MobileAppVersion::class);
    }

    public function findForPlatform(string $platform): ?MobileAppVersion
    {
        return $this->findOneBy(['platform' => strtolower(trim($platform))]);
    }
}

