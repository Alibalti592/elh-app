<?php

namespace App\Entity;

use App\Repository\SalatShareRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalatShareRepository::class)]
class SalatShare
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'salatShares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salat $salat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSalat(): ?Salat
    {
        return $this->salat;
    }

    public function setSalat(?Salat $salat): static
    {
        $this->salat = $salat;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
