<?php

namespace App\Entity\Thread;

use App\Doctrine\Sqid;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Enum\Thread\ThreadStatusEnum;
use App\Repository\Thread\ThreadRepository;
use Assert\Assertion;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
class Thread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThreadStatus $status = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, ThreadMessage>
     */
    #[ORM\OneToMany(targetEntity: ThreadMessage::class, mappedBy: 'thread')]
    private Collection $threadMessages;

    public function __construct(
        string             $title,
        string             $slug,
        User               $createdBy,
        Project            $project,
        ThreadStatus       $status,
        DateTimeImmutable $createdAt
    ) {
        $this->title = $title;
        $this->setSlug($slug);
        $this->createdBy = $createdBy;
        $this->project = $project;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->threadMessages = new ArrayCollection();
    }

    public function getId(): ?Sqid
    {
        return $this->id;
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function isOpen(): bool
    {
        return $this->getStatus()->getId() === ThreadStatusEnum::Open->value;
    }

    public function isClosed(): bool
    {
        return $this->getStatus()->getId() === ThreadStatusEnum::Closed->value;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): ?ThreadStatus
    {
        return $this->status;
    }

    public function setStatus(?ThreadStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return Collection<int, ThreadMessage>
     */
    public function getThreadMessages(): Collection
    {
        return $this->threadMessages;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    private function setSlug(string $slug): void
    {
        Assertion::regex($slug, '/^[\w-]+$/');

        $this->slug = $slug;
    }
}
