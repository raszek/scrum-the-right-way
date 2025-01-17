<?php

namespace App\Entity\Thread;

use App\Doctrine\Sqid;
use App\Entity\User\User;
use App\Repository\Thread\ThreadMessageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\UniqueConstraint(columns: ['id', 'number'])]
#[ORM\Entity(repositoryClass: ThreadMessageRepository::class)]
class ThreadMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column]
    private int $number;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'threadMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Thread $thread = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $content,
        int $number,
        Thread $thread,
        User $createdBy,
        DateTimeImmutable $createdAt
    ) {
        $this->content = $content;
        $this->thread = $thread;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->number = $number;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getIssueTitle(): string
    {
        return $this->getThread()->getTitle() .' #'.$this->getNumber();
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
