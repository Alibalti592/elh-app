<?php

namespace App\Entity;

use App\Repository\ResetpasswordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResetpasswordRepository::class)]
class Resetpassword
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne()]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?\DateTime $expireAt;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expireAt = new \DateTime('+10 minutes');
        $digits = 6;
        $code = random_int(pow(10, $digits - 1), pow(10, $digits) - 1);
        $this->code = $code;
    }

    public function  isExpired()
    {
        $now = new \DateTime('now');
        $nowStamp = $now->getTimestamp();
        return  $nowStamp > $this->expireAt->getTimestamp();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(\DateTime $expireAt): static
    {
        $this->expireAt = $expireAt;

        return $this;
    }
}
