<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index(columns: ['firstname', 'lastname'], flags: ['fulltext'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Location $location = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Media $photo = null;

    #[ORM\OneToMany(mappedBy: 'managedBy', targetEntity: Pompe::class)]
    private Collection $pompes;

    #[ORM\Column(nullable: true)]
    private ?bool $showDetteInfos;

    #[ORM\Column(length: 255)]
    private ?string $phonePrefix = "+33";

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 10, options: ["default" => "unactive"])]
    private ?string $status = 'unactive';

    // 🔐 Nouveau: code OTP (6 chiffres max)
    #[ORM\Column(length: 6, nullable: true)]
    private ?string $otpCode = null;

    // 🔐 Nouveau: date d’expiration de l’OTP
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $otpExpiresAt = null;

    public function __construct()
    {
        $this->createAt = new \DateTimeImmutable();
        $this->lastLogin = new \DateTime();
        $this->pompes = new ArrayCollection();
        $this->notifPlanneds = new ArrayCollection();
        $this->showDetteInfos = true;
        $this->status = 'unactive';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, ['active', 'unactive'])) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getFullname(): ?string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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

    public function getPhoto(): ?Media
    {
        return $this->photo;
    }

    public function setPhoto(?Media $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection<int, Pompe>
     */
    public function getPompes(): Collection
    {
        return $this->pompes;
    }

    public function addPompe(Pompe $pompe): static
    {
        if (!$this->pompes->contains($pompe)) {
            $this->pompes->add($pompe);
            $pompe->setManagedBy($this);
        }

        return $this;
    }

    public function removePompe(Pompe $pompe): static
    {
        if ($this->pompes->removeElement($pompe)) {
            // set the owning side to null (unless already changed)
            if ($pompe->getManagedBy() === $this) {
                $pompe->setManagedBy(null);
            }
        }

        return $this;
    }

    public function isShowDetteInfos()
    {
        if (is_null($this->showDetteInfos)) {
            return true;
        }
        return $this->showDetteInfos;
    }

    public function setShowDetteInfos(bool $showDetteInfos): static
    {
        $this->showDetteInfos = $showDetteInfos;

        return $this;
    }

    public function getPhonePrefix(): ?string
    {
        if (is_null($this->phonePrefix) || $this->phonePrefix == "") {
            return "+33";
        }
        return $this->phonePrefix;
    }

    public function setPhonePrefix(string $phonePrefix): static
    {
        $this->phonePrefix = $phonePrefix;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    // 🔐 Getters / setters OTP

    public function getOtpCode(): ?string
    {
        return $this->otpCode;
    }

    public function setOtpCode(?string $otpCode): static
    {
        $this->otpCode = $otpCode;

        return $this;
    }

    public function getOtpExpiresAt(): ?\DateTimeInterface
    {
        return $this->otpExpiresAt;
    }

    public function setOtpExpiresAt(?\DateTimeInterface $otpExpiresAt): static
    {
        $this->otpExpiresAt = $otpExpiresAt;

        return $this;
    }
}
