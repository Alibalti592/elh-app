<?php

namespace App\Entity;

use App\Repository\PompeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PompeRepository::class)]
class Pompe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\Column]
    private bool $online = false;

    #[ORM\Column]
    private bool $validated = false;

    #[ORM\ManyToOne(inversedBy: 'pompes')]
    private ?User $managedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailpro = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $phonePrefix  =  "+33";

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneUrgence = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $prefixUrgence = "+33";

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getManagedBy(): ?User
    {
        return $this->managedBy;
    }

    public function setManagedBy(?User $managedBy): static
    {
        $this->managedBy = $managedBy;

        return $this;
    }

    public function getPhone(): ?string
    {
        return is_null($this->phone) ? "" : $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getEmailpro(): ?string
    {
        return is_null($this->emailpro) ? "" : $this->emailpro;
    }

    public function setEmailpro(?string $emailpro): static
    {
        $this->emailpro = $emailpro;

        return $this;
    }

    public function getPhonePrefix(): ?string
    {
        return is_null($this->phonePrefix) ? "+33" : $this->phonePrefix;
    }

    public function setPhonePrefix(?string $phonePrefix): static
    {
        $this->phonePrefix = $phonePrefix;

        return $this;
    }

    public function getPhoneUrgence(): ?string
    {
        return is_null($this->phoneUrgence) ? "" : $this->phoneUrgence;
    }

    public function setPhoneUrgence(?string $phoneUrgence): static
    {
        $this->phoneUrgence = $phoneUrgence;

        return $this;
    }

    public function getPrefixUrgence(): ?string
    {
        return is_null($this->prefixUrgence) ? "+33" : $this->prefixUrgence;
    }

    public function setPrefixUrgence(?string $prefixUrgence): static
    {
        $this->prefixUrgence = $prefixUrgence;

        return $this;
    }
}
