<?php

namespace App\Entity\Event;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Repository\Event\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/** @template T */
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Issue $issue = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'json')]
    private ?array $params = null;

    /**
     * @var T|null
     */
    private mixed $eventData = null;

    public function __construct(
        string $name,
        array $params,
        Project $project,
        DateTimeImmutable $createdAt,
        ?User $createdBy = null,
        ?Issue $issue = null
    ) {
        $this->name = $name;
        $this->project = $project;
        $this->createdAt = $createdAt;
        $this->createdBy = $createdBy;
        $this->params = $params;
        $this->issue = $issue;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @return T
     */
    public function getData(): mixed
    {
        return $this->eventData;
    }

    /**
     * @param T $eventData
     * @return void
     */
    public function setData(mixed $eventData): void
    {
        $this->eventData = $eventData;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }
}
