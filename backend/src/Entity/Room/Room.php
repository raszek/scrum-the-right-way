<?php

namespace App\Entity\Room;

use App\Doctrine\Sqid;
use App\Entity\Project\Project;
use App\Repository\Room\RoomRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    /**
     * @var Collection<int, RoomIssue>
     */
    #[ORM\OneToMany(targetEntity: RoomIssue::class, mappedBy: 'room')]
    private Collection $roomIssues;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct(
        Project $project,
        DateTimeImmutable $createdAt
    ) {
        $this->project = $project;
        $this->createdAt = $createdAt;
        $this->roomIssues = new ArrayCollection();
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @return Collection<int, RoomIssue>
     */
    public function getRoomIssues(): Collection
    {
        return $this->roomIssues;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
