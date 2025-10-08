<?php

namespace App\Entity;

use App\Repository\DeceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeceRepository::class)]
class Dece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $afiliation = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Location $location = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $notifPf = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    public function __construct() {
         $this->createdAt = new \DateTimeImmutable();
    }

    public function setFromUI($arr)
    {
        $this->firstname = $arr['firstname'];
        $this->lastname = $arr['lastname'];
        $this->lieu = $arr['lieu'];
        $this->afiliation = $arr['afiliation'];
        $this->phone = $arr['phone'];
        $this->notifPf = boolval($arr['notifPf']);
        $date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAfiliation(): ?string
    {
        return $this->afiliation;
    }

    public function setAfiliation(string $afiliation): static
    {
        $this->afiliation = $afiliation;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

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

    public function isNotifPf(): bool
    {
        if(is_null($this->notifPf)) {
            return false;
        }
        return $this->notifPf;
    }

    public function setNotifPf(?bool $notifPf): static
    {
        $this->notifPf = $notifPf;

        return $this;
    }

    public function getPhone(): ?string
    {
        if(is_null($this->phone)) {
            return "";
        }
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
