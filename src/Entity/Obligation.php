<?php

namespace App\Entity;

use App\Repository\ObligationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Tranche; 
use App\Entity\User;

#[ORM\Entity(repositoryClass: ObligationRepository::class)]
class Obligation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $remainingAmount;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(length: 255)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $raison = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $delay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $conditonType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $moyen = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 255)]
    private string $type = 'jed';

    #[ORM\ManyToOne]
    private ?User $relatedTo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\OneToMany(mappedBy: 'obligation', targetEntity: Tranche::class, cascade: ['persist', 'remove'])]
    private Collection $tranches;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'processing';
        $this->tranches = new ArrayCollection();
    }

    // --- Getters / Setters ---

    public function getId(): ?int { return $this->id; }
public function getEmprunteurId(): ?int
{
    return $this->relatedTo?->getId();
}
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
    public function getRemainingAmount(): float { return (float) $this->remainingAmount; }
    public function setRemainingAmount(float $remainingAmount): self { $this->remainingAmount = (string) $remainingAmount; return $this; }

    public function getFirstname(): string { return $this->firstname ?? ""; }
    public function setFirstname(?string $firstname): static { $this->firstname = $firstname; return $this; }

    public function getLastname(): string { return $this->lastname ?? ""; }
    public function setLastname(?string $lastname): static { $this->lastname = $lastname; return $this; }

    public function getAdress(): string { return $this->adress ?? ""; }
    public function setAdress(?string $adress): static { $this->adress = $adress; return $this; }

    public function getTel(): string { return $this->tel ?? ""; }
    public function setTel(?string $tel): static { $this->tel = $tel; return $this; }

    public function getAmount(): string { return $this->amount ?? ""; }
    public function setAmount(string $amount): static { $this->amount = $amount; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(?\DateTimeInterface $date): static { $this->date = $date; return $this; }

    public function getRaison(): string { return $this->raison ?? ""; }
    public function setRaison(?string $raison): static { $this->raison = $raison; return $this; }

    public function getDelay(): string { return $this->delay ?? ""; }
    public function setDelay(?string $delay): static { $this->delay = $delay; return $this; }

    public function getConditonType(): ?string { return $this->conditonType; }
    public function setConditonType(?string $conditonType): static { $this->conditonType = $conditonType; return $this; }

    public function getMoyen(): ?string { return $this->moyen; }
    public function setMoyen(?string $moyen): static { $this->moyen = $moyen; return $this; }

    public function getCreatedBy(): ?User { return $this->createdBy; }
    public function setCreatedBy(?User $createdBy): static { $this->createdBy = $createdBy; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getRelatedTo(): ?User { return $this->relatedTo; }
    public function setRelatedTo(?User $relatedTo): static { $this->relatedTo = $relatedTo; return $this; }

    public function getDateStart(): ?\DateTimeInterface { return $this->dateStart; }
    public function setDateStart(?\DateTimeInterface $dateStart): static { $this->dateStart = $dateStart; return $this; }

    public function getStatus(): string { return $this->status ?? 'ini'; }
    public function setStatus(?string $status): static { $this->status = $status; return $this; }

    public function getDeletedAt(): ?\DateTimeImmutable { return $this->deletedAt; }
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static { $this->deletedAt = $deletedAt; return $this; }

    public function getTranches(): Collection { return $this->tranches; }
    public function addTranche(Tranche $tranche): self { if (!$this->tranches->contains($tranche)) { $this->tranches->add($tranche); $tranche->setObligation($this); } return $this; }
    public function removeTranche(Tranche $tranche): self { if ($this->tranches->removeElement($tranche)) { if ($tranche->getObligation() === $this) { $tranche->setObligation(null); } } return $this; }

    public function setFromUI(array $datas, bool $isEdit = false): void {
        if (!$isEdit) {
            $this->firstname = $datas['firstname'] ?? null;
            $this->lastname = $datas['lastname'] ?? null;
            $this->tel = $datas['tel'] ?? null;
            $this->type = $datas['type'] ?? 'jed';
        }
        $this->adress = $datas['adress'] ?? null;
        $this->amount = $this->transformToFloat($datas['amount'] ?? '0');
        $this->remainingAmount = $this->transformToFloat($datas['amount'] ?? '0');
        $this->raison = $datas['raison'] ?? null;
        $this->date = isset($datas['date']) ? new \DateTime($datas['date']) : null;
        $this->delay = $datas['delay'] ?? null;
        $this->conditonType = $datas['conditonType'] ?? null;
        $this->moyen = $datas['moyen'] ?? null;
        $this->dateStart = isset($datas['dateStart']) ? new \DateTime($datas['dateStart']) : null;
        $this->fileUrl = $datas['fileUrl'] ?? null;
        if (isset($datas['relatedUserId'])) {
    // fetch User entity from repository
    $user = $userRepository->find($datas['relatedUserId']);
    $this->relatedTo = $user;
}
    }

    private function transformToFloat($input): float {
        $cleanedInput = preg_replace('/[^\d.,]/', '', $input);
        $cleanedInput = str_replace(',', '.', $cleanedInput);
        return (float)$cleanedInput;
    }
}
