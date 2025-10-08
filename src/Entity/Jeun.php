<?php

namespace App\Entity;

use App\Repository\JeunRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JeunRepository::class)]
class Jeun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $nbDays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $selectedYear = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $jeunNbDaysR = null;

    public function __construct()
    {
        $this->nbDays = 0;
        $this->jeunNbDaysR = 0;
        $date = new \DateTime();
        $this->selectedYear = intval($date->format('Y'));
        $this->text = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbDays(): ?int
    {
        return $this->nbDays;
    }

    public function setNbDays(int $nbDays): static
    {
        $this->nbDays = $nbDays;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getSelectedYear(): ?int
    {
        return $this->selectedYear;
    }

    public function setSelectedYear(int $selectedYear): static
    {
        $this->selectedYear = $selectedYear;

        return $this;
    }

    public function getJeunNbDaysR(): ?int
    {
        return $this->jeunNbDaysR;
    }

    public function setJeunNbDaysR(int $jeunNbDaysR): static
    {
        $this->jeunNbDaysR = $jeunNbDaysR;

        return $this;
    }
}
