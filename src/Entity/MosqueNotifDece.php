<?php

namespace App\Entity;

use App\Repository\MosqueNotifDeceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MosqueNotifDeceRepository::class)]
class MosqueNotifDece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mosque $mosque = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dece $dece = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?bool $showOnPage = false;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMosque(): ?Mosque
    {
        return $this->mosque;
    }

    public function setMosque(?Mosque $mosque): static
    {
        $this->mosque = $mosque;

        return $this;
    }

    public function getDece(): ?Dece
    {
        return $this->dece;
    }

    public function setDece(?Dece $dece): static
    {
        $this->dece = $dece;

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

    public function isShowOnPage(): bool
    {
        if(is_null($this->showOnPage)) {
            return false;
        }
        return $this->showOnPage;
    }

    public function setShowOnPage(bool $showOnPage): static
    {
        $this->showOnPage = $showOnPage;

        return $this;
    }
}
