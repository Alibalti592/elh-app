<?php

namespace App\Entity;

use App\Repository\MobileAppVersionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MobileAppVersionRepository::class)]
#[ORM\Table(
    name: 'mobile_app_version',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_mobile_app_version_platform', columns: ['platform']),
    ]
)]
class MobileAppVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $platform = '';

    #[ORM\Column(length: 30)]
    private string $version = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): static
    {
        $this->platform = strtolower(trim($platform));

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = trim($version);

        return $this;
    }
}
