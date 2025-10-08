<?php
namespace App\Entity;

use App\Repository\TrancheRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Obligation;
use App\Entity\User;


#[ORM\Entity(repositoryClass: TrancheRepository::class)]
class Tranche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tranches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Obligation $obligation = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $paidAt;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'en attente'; // <-- nouveau champ pour gérer le statut

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $emprunteur = null; // <-- utilisateur qui doit accepter/refuser
#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $fileUrl = null;

public function getFileUrl(): ?string
{
    return $this->fileUrl;
}

public function setFileUrl(?string $fileUrl): self
{
    $this->fileUrl = $fileUrl;
    return $this;
}
    // --- Getters & Setters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObligation(): ?Obligation
    {
        return $this->obligation;
    }

    public function setObligation(?Obligation $obligation): self
    {
        $this->obligation = $obligation;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPaidAt(): \DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeInterface $paidAt): self
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getEmprunteur(): ?User
    {
        return $this->emprunteur;
    }

    public function setEmprunteur(?User $emprunteur): self
    {
        $this->emprunteur = $emprunteur;
        return $this;
    }
}
