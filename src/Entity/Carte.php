<?php

namespace App\Entity;

use App\Repository\CarteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarteRepository::class)]
class Carte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $afiliation = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deathDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    //death || malade
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $locationName = null;

    #[ORM\Column(length: 25)]
    private ?string $onmyname = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $phonePrefix = null;

    #[ORM\OneToOne(inversedBy: 'carte', cascade: ['persist', 'remove'])]
    private ?Salat $salat = null;

    public function setFromUI($arr)
    {
        $this->type = $arr['type'];
        $this->firstname = $arr['firstname'];
        $this->lastname = $arr['lastname'];
        $this->afiliation = $arr['afiliation'];
        $this->locationName = $arr['locationName'];
        $this->onmyname = $arr['onmyname'];
        $this->phone = $arr['phone'];
        $this->phonePrefix = $arr['phonePrefix'];
        if(isset($arr['date'])) {
            $this->deathDate = new \DateTimeImmutable($arr['date']);
        }
        $content = "";
        if(!is_null($arr['content'])) {
            $content = strlen($arr['content']) > 1000 ? mb_substr($arr['content'], 0, 1000) : $arr['content'];
        }
        $this->content = $content;

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

    public function getDeathDate(): ?\DateTimeInterface
    {
        return $this->deathDate;
    }

    public function setDeathDate(\DateTimeInterface $deathDate): static
    {
        $this->deathDate = $deathDate;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLocationName(): string
    {
        return is_null($this->locationName) ? "" : $this->locationName;
    }

    public function setLocationName(?string $locationName): static
    {
        $this->locationName = $locationName;

        return $this;
    }

    public function getOnmyname(): ?string
    {
        return $this->onmyname;
    }

    public function setOnmyname(string $onmyname): static
    {
        $this->onmyname = $onmyname;

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

    public function getPhonePrefix(): ?string
    {
        return is_null($this->phonePrefix) ? "+33" : $this->phonePrefix;
    }

    public function setPhonePrefix(?string $phonePrefix): static
    {
        $this->phonePrefix = $phonePrefix;

        return $this;
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

    public function setFromSalat(Salat $salat)
    {
        $this->afiliation = $salat->getAfiliation();
        $this->firstname = $salat->getFirstname();
        $this->lastname = $salat->getLastname();
        $this->deathDate = $salat->getCeremonyAt();
        $this->content = "";
        $this->locationName = "";
        $this->onmyname = "toother";
        $this->type = "salat";
    }
}
