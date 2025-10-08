<?php

namespace App\Entity;

use App\Repository\SalatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalatRepository::class)]
class Salat
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

    #[ORM\Column]
    private ?\DateTimeImmutable $ceremonyAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Mosque $mosque = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mosqueName = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Location $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $cimetary = null;

    #[ORM\OneToOne(mappedBy: 'salat', cascade: ['persist', 'remove'])]
    private ?Carte $carte = null;

    #[ORM\OneToMany(mappedBy: 'salat', targetEntity: SalatShare::class, orphanRemoval: true)]
    private Collection $salatShares;


    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->salatShares = new ArrayCollection();
    }

    public function setFromUI($arr)
    {
        $this->firstname = $arr['firstname'];
        $this->lastname = $arr['lastname'];
        $this->afiliation = $arr['afiliation'];
        $this->cimetary = $arr['cimetary'];
        $this->ceremonyAt = new \DateTimeImmutable($arr['date']);
        $content = "";
        if(!is_null($arr['content'])) {
            $content = strlen($arr['content']) > 300 ? mb_substr($arr['content'], 0, 300) : $arr['content'];
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

    public function getCeremonyAt(): ?\DateTimeImmutable
    {
        return $this->ceremonyAt;
    }

    public function setCeremonyAt(\DateTimeImmutable $ceremonyAt): static
    {
        $this->ceremonyAt = $ceremonyAt;

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

    public function getMosque(): ?Mosque
    {
        return $this->mosque;
    }

    public function setMosque(?Mosque $mosque): static
    {
        $this->mosque = $mosque;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCimetary(): string
    {
        if(is_null($this->cimetary)) {
            return "";
        }
        return $this->cimetary;
    }

    public function setCimetary(string $cimetary): static
    {
        $this->cimetary = $cimetary;

        return $this;
    }

    public function getCarte(): ?Carte
    {
        return $this->carte;
    }

    public function setCarte(?Carte $carte): static
    {
        // unset the owning side of the relation if necessary
        if ($carte === null && $this->carte !== null) {
            $this->carte->setSalat(null);
        }

        // set the owning side of the relation if necessary
        if ($carte !== null && $carte->getSalat() !== $this) {
            $carte->setSalat($this);
        }

        $this->carte = $carte;

        return $this;
    }

    /**
     * @return Collection<int, SalatShare>
     */
    public function getSalatShares(): Collection
    {
        return $this->salatShares;
    }

    public function addSalatShare(SalatShare $salatShare): static
    {
        if (!$this->salatShares->contains($salatShare)) {
            $this->salatShares->add($salatShare);
            $salatShare->setSalat($this);
        }

        return $this;
    }

    public function removeSalatShare(SalatShare $salatShare): static
    {
        if ($this->salatShares->removeElement($salatShare)) {
            // set the owning side to null (unless already changed)
            if ($salatShare->getSalat() === $this) {
                $salatShare->setSalat(null);
            }
        }

        return $this;
    }

    public function getMosqueName()
    {
        return $this->mosqueName;
    }

    public function setMosqueName($mosqueName)
    {
        $this->mosqueName = $mosqueName;
    }


}
