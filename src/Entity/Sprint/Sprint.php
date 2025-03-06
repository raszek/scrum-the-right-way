<?php

namespace App\Entity\Sprint;

use App\Doctrine\Sqid;
use App\Entity\Project\Project;
use App\Repository\Sprint\SprintRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SprintRepository::class)]
class Sprint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $endedAt = null;

    #[ORM\Column]
    private bool $isCurrent;

    #[ORM\ManyToOne(inversedBy: 'sprints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    /**
     * @var Collection<int, SprintGoal>
     */
    #[ORM\OneToMany(targetEntity: SprintGoal::class, mappedBy: 'sprint')]
    private Collection $sprintGoals;

    public function __construct(
        int $number,
        bool $isCurrent,
        Project $project,
    ) {
        $this->sprintGoals = new ArrayCollection();
        $this->number = $number;
        $this->project = $project;
        $this->isCurrent = $isCurrent;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getStartedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, SprintGoal>
     */
    public function getSprintGoals(): Collection
    {
        return $this->sprintGoals;
    }

    public function addSprintGoal(SprintGoal $sprintGoal): static
    {
        if (!$this->sprintGoals->contains($sprintGoal)) {
            $this->sprintGoals->add($sprintGoal);
            $sprintGoal->setSprint($this);
        }

        return $this;
    }

    public function removeSprintGoal(SprintGoal $sprintGoal): static
    {
        if ($this->sprintGoals->removeElement($sprintGoal)) {
            // set the owning side to null (unless already changed)
            if ($sprintGoal->getSprint() === $this) {
                $sprintGoal->setSprint(null);
            }
        }

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): void
    {
        $this->isCurrent = $isCurrent;
    }
}
