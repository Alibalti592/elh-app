<?php
namespace App\Entity;

use App\Entity\Coaching\ChatBubble;
use App\Repository\ChatThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatThreadRepository::class)]
class ChatThread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private $type;

    #[ORM\Column(type: 'datetime')]
    private $lastUpdate;

    #[ORM\OneToOne(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    private $image;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'chatThread', targetEntity: ChatMessage::class, orphanRemoval: true)]
    private $messages;

    #[ORM\OneToOne(targetEntity: ChatMessage::class, cascade: ['persist', 'remove'])]
    private $lastMessage;


    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: ChatParticipant::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private $participants;


    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private $createdBy;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $reference;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;


    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->lastUpdate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if(!in_array($type, ['simple', 'group'])) {
            $type = 'group';
        }
        $this->type = $type;
        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTimeInterface $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(?Media $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|ChatMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ChatMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setChatThread($this);
        }

        return $this;
    }

    public function removeMessage(ChatMessage $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChatThread() === $this) {
                $message->setChatThread(null);
            }
        }

        return $this;
    }

    public function getLastMessage(): ?ChatMessage
    {
        return $this->lastMessage;
    }

    public function setLastMessage(?ChatMessage $lastMessage): self
    {
        $this->lastMessage = $lastMessage;

        return $this;
    }

    /**
     * @return Collection|ChatParticipant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ChatParticipant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setThread($this);
        }

        return $this;
    }

    public function removeParticipant(ChatParticipant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getThread() === $this) {
                $participant->setThread(null);
            }
        }

        return $this;
    }

    /**
     * Set createdBy
     *
     * @param \App\Entity\User $createdBy
     *
     * @return ChatBubble
     */
    public function setCreatedBy(\App\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \App\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
