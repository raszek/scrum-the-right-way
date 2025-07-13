<?php

namespace App\Entity\User;

use App\Doctrine\Sqid;
use App\Entity\Issue\Issue;
use App\Entity\Project\ProjectMember;
use App\Enum\User\UserRoleEnum;
use App\Helper\ArrayHelper;
use App\Repository\User\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $activationCode = null;

    #[ORM\Column(length: 255)]
    private ?string $passwordHash = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetPasswordCode = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Issue $inProgressIssue = null;

    #[ORM\OneToMany(targetEntity: ProjectMember::class, mappedBy: 'user')]
    private Collection $projectMembers;

    #[ORM\OneToMany(targetEntity: UserNotification::class, mappedBy: 'forUser')]
    private Collection $notifications;

    private ?string $plainPassword = null;

    public function __construct(
        string $email,
        string $plainPassword,
        string $firstName,
        string $lastName,
        DateTimeImmutable $createdAt,
        ?string $activationCode = null,
    ) {
        $this->email = $email;
        $this->plainPassword = $plainPassword;
        $this->createdAt = $createdAt;
        $this->activationCode = $activationCode;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->projectMembers = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?Sqid
    {
        return $this->id;
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

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getActivationCode(): ?string
    {
        return $this->activationCode;
    }

    public function setActivationCode(?string $activationCode): static
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    public function getResetPasswordCode(): ?string
    {
        return $this->resetPasswordCode;
    }

    public function setResetPasswordCode(?string $resetPasswordCode): static
    {
        $this->resetPasswordCode = $resetPasswordCode;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getPassword(): ?string
    {
        return $this->getPasswordHash();
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->activationCode === null;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getDomainName(): string
    {
        return sprintf('%s "%s"', $this->getFullName(), $this->getEmail());
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return Collection<ProjectMember>
     */
    public function getProjectMembers(): Collection
    {
        return $this->projectMembers;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function getNotificationsToSend(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isRead', false))
            ->andWhere(Criteria::expr()->eq('isSentEmail', false));

        return $this->notifications->matching($criteria);
    }

    public function getInProgressIssue(): ?Issue
    {
        return $this->inProgressIssue;
    }

    public function setInProgressIssue(?Issue $inProgressIssue): void
    {
        $this->inProgressIssue = $inProgressIssue;
    }

    public function isAdmin(): bool
    {
        return ArrayHelper::inArray(UserRoleEnum::Admin->value, $this->roles);
    }
}
