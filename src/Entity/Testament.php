<?php

namespace App\Entity;

use App\Repository\TestamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestamentRepository::class)]
class Testament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $family = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $goods = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToMany(mappedBy: 'testament', targetEntity: TestamentShare::class, orphanRemoval: true)]
    private Collection $testamentShares;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $toilette = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fixe = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastwill = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updateAt = new \DateTime();
        $this->testamentShares = new ArrayCollection();
    }

    public function setFromUI($datas) {
        $this->location = $datas['location'];
        $this->family = $datas['family'];
        $this->goods = $datas['goods'];
        $this->toilette = $datas['toilette'];
        $this->fixe = $datas['fixe'];
        $this->lastwill = $datas['lastwill'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(?string $family): static
    {
        $this->family = $family;

        return $this;
    }

    public function getGoods(): ?string
    {
        return $this->goods;
    }

    public function setGoods(?string $goods): static
    {
        $this->goods = $goods;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    /**
     * @return Collection<int, TestamentShare>
     */
    public function getTestamentShares(): Collection
    {
        return $this->testamentShares;
    }

    public function addTestamentShare(TestamentShare $testamentShare): static
    {
        if (!$this->testamentShares->contains($testamentShare)) {
            $this->testamentShares->add($testamentShare);
            $testamentShare->setTestament($this);
        }

        return $this;
    }

    public function removeTestamentShare(TestamentShare $testamentShare): static
    {
        if ($this->testamentShares->removeElement($testamentShare)) {
            // set the owning side to null (unless already changed)
            if ($testamentShare->getTestament() === $this) {
                $testamentShare->setTestament(null);
            }
        }

        return $this;
    }

    public function getToilette(): ?string
    {
        return $this->toilette;
    }

    public function setToilette(?string $toilette): static
    {
        $this->toilette = $toilette;

        return $this;
    }

    public function getFixe(): ?string
    {
        return $this->fixe;
    }

    public function setFixe(?string $fixe): static
    {
        $this->fixe = $fixe;

        return $this;
    }

    public function getLastwill(): ?string
    {
        return $this->lastwill;
    }

    public function setLastwill(?string $lastwill): static
    {
        $this->lastwill = $lastwill;

        return $this;
    }
}
