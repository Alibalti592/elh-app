<?php

namespace App\Entity;

use App\Repository\MaraudeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaraudeRepository::class)]
class Maraude
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private bool $online = false;

    #[ORM\Column]
    private bool $validated = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToOne]
    private ?User $managedBy = null;


    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setFromUI($arr)
    {
        if(isset($arr['dateVue'])) {
            $dateTime = new \DateTime($arr['dateVue']);
            $dateTime->setTimezone(new \DateTimeZone('Europe/Paris'));
            $this->date = $dateTime;
        } else {
            $this->date = new \DateTimeImmutable($arr['date']);
        }

        $this->setDescription($arr['description']);

    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): static
    {
        $this->validated = $validated;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getManagedBy(): ?User
    {
        return $this->managedBy;
    }

    public function setManagedBy(?User $managedBy): static
    {
        $this->managedBy = $managedBy;

        return $this;
    }
}
