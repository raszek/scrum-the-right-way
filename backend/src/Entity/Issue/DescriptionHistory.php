<?php

namespace App\Entity\Issue;

use App\Doctrine\Sqid;
use App\Repository\Issue\DescriptionHistoryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DescriptionHistoryRepository::class)]
class DescriptionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\ManyToOne(inversedBy: 'descriptionHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $changes = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct(
        Issue $issue,
        ?array $changes,
        DateTimeImmutable $createdAt
    ) {
        $this->issue = $issue;
        $this->changes = $changes;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }
    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function getChanges(): ?array
    {
        return $this->changes;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
