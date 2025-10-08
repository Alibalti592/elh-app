<?php

namespace App\Entity;

use App\Repository\PardonShareRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PardonShareRepository::class)]
class PardonShare
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pardonShares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pardon $pardon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $shareWith = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPardon(): ?Pardon
    {
        return $this->pardon;
    }

    public function setPardon(?Pardon $pardon): static
    {
        $this->pardon = $pardon;

        return $this;
    }

    public function getShareWith(): ?User
    {
        return $this->shareWith;
    }

    public function setShareWith(?User $shareWith): static
    {
        $this->shareWith = $shareWith;

        return $this;
    }
}
