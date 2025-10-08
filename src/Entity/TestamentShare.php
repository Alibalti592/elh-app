<?php

namespace App\Entity;

use App\Repository\TestamentShareRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestamentShareRepository::class)]
class TestamentShare
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'testamentShares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Testament $testament = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTestament(): ?Testament
    {
        return $this->testament;
    }

    public function setTestament(?Testament $testament): static
    {
        $this->testament = $testament;

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
