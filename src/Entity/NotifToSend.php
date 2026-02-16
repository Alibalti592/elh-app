<?php

namespace App\Entity;

use App\Repository\NotifToSendRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotifToSendRepository::class)]
class NotifToSend
{
    private const PRAY_REMINDER_OFFSET_SECONDS = 60 * 15;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $datas = null;

    #[ORM\Column]
    private ?\DateTime $sendAt = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $view = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    #[ORM\Column(length: 191, nullable: true, unique: true)]
    private ?string $dedupeKey = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isRead = null;

    public function setForDece($currentUser, Dece $dece)
    {
        $sendAt = new \DateTime();
        $sendAt->modify('+24 hours');
        $this->setUser($currentUser);
        $this->setTitle("Démarches administratives");
        $this->setMessage("Consultez les démarches administratives suite au décès de ".$dece->getFirstname(). " ".$dece->getLastname());
        $this->setSendAt($sendAt);
        $this->setType('dece-h24-administratif');
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function setForPrayFromUI($currentUser, $praytimeUI)
    {
        $sendAt = new \DateTime();
        $sendAt->setTimezone(new \DateTimeZone("Europe/Paris"));
        $timestamp = (int) $praytimeUI['timestamp'] - self::PRAY_REMINDER_OFFSET_SECONDS;
        $sendAt->setTimestamp($timestamp);
        $this->setUser($currentUser);
        $this->setTitle("Rappel de prière");
        $this->setView("pray");
        $this->setMessage("Vous entrez bientôt dans le temps de prière de la Salât : ".$praytimeUI['label']);
        $this->setSendAt($sendAt);
        $this->setType($praytimeUI['key']);
        if ($currentUser instanceof User) {
            $this->setDedupeKey(self::buildPrayDedupeKey($currentUser, (string) $praytimeUI['key'], $timestamp));
        }
    }

    public static function buildPrayDedupeKey(User $user, string $prayKey, int $timestamp): string
    {
        return sprintf('pray|%d|%s|%d', (int) $user->getId(), $prayKey, $timestamp);
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDatas(): ?string
    {
        return $this->datas;
    }

    public function setDatas(?string $datas): static
    {
        $this->datas = $datas;

        return $this;
    }

    public function getSendAt(): ?\DateTime
    {
        return $this->sendAt;
    }

    public function setSendAt(\DateTime $sendAt): static
    {
        $this->sendAt = $sendAt;

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

    public function getView(): ?string
    {
        return $this->view;
    }

    public function setView(?string $view): void
    {
        $this->view = $view;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function getDedupeKey(): ?string
    {
        return $this->dedupeKey;
    }

    public function setDedupeKey(?string $dedupeKey): static
    {
        $this->dedupeKey = $dedupeKey;
        return $this;
    }
}
