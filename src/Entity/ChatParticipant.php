<?php

namespace App\Entity;

use App\Repository\ChatParticipantRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_relation', columns: ['user_id', 'thread_id'])]
#[ORM\Entity(repositoryClass: ChatParticipantRepository::class)]
class ChatParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: ChatThread::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private $thread;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getThread(): ?ChatThread
    {
        return $this->thread;
    }

    public function setThread(?ChatThread $thread): self
    {
        $this->thread = $thread;

        return $this;
    }
}
