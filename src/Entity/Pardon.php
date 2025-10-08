<?php

namespace App\Entity;

use App\Repository\PardonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PardonRepository::class)]
class Pardon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\OneToMany(mappedBy: 'pardon', targetEntity: PardonShare::class, orphanRemoval: true)]
    private Collection $pardonShares;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->pardonShares = new ArrayCollection();
    }

    public function setFromUI($arr)
    {
        $this->firstname = $arr['firstname'];
        $this->lastname = $arr['lastname'];
        $this->content = $arr['content'];
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection<int, PardonShare>
     */
    public function getPardonShares(): Collection
    {
        return $this->pardonShares;
    }

    public function addPardonShare(PardonShare $pardonShare): static
    {
        if (!$this->pardonShares->contains($pardonShare)) {
            $this->pardonShares->add($pardonShare);
            $pardonShare->setPardon($this);
        }

        return $this;
    }

    public function removePardonShare(PardonShare $pardonShare): static
    {
        if ($this->pardonShares->removeElement($pardonShare)) {
            // set the owning side to null (unless already changed)
            if ($pardonShare->getPardon() === $this) {
                $pardonShare->setPardon(null);
            }
        }

        return $this;
    }
}
