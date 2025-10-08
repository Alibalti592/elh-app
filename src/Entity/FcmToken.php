<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FcmToken
 */
#[ORM\Table(name: 'fcm_token')]
#[ORM\Entity(repositoryClass: 'App\Repository\FcmTokenRepository')]
class FcmToken
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'fcmToken', type: 'string', length: 255)]
    private $fcmToken;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    private $user;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviceId = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fcmToken.
     *
     * @param string $fcmToken
     *
     * @return FcmToken
     */
    public function setFcmToken($fcmToken)
    {
        $this->fcmToken = $fcmToken;

        return $this;
    }

    /**
     * Get fcmToken.
     *
     * @return string
     */
    public function getFcmToken()
    {
        return $this->fcmToken;
    }

    /**
     * Set user.
     *
     * @param \App\Entity\User|null $user
     *
     * @return FcmToken
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }


    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): static
    {
        $this->deviceId = $deviceId;

        return $this;
    }
}