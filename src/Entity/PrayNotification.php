<?php

namespace App\Entity;

use App\Repository\PrayNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrayNotificationRepository::class)]
class PrayNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $prays = null;

    #[ORM\Column]
    private bool $notifAdded = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPrays()
    {
        return json_decode($this->prays, true);
    }

    public function setPrays($prays): static
    {
        $this->prays = json_encode($prays);

        return $this;
    }

    public function isNotifAdded(): ?bool
    {
        return $this->notifAdded;
    }

    public function setNotifAdded(bool $notifAdded): static
    {
        $this->notifAdded = $notifAdded;

        return $this;
    }
}
