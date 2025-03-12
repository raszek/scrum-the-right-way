<?php

namespace App\Entity\Sprint;

use App\Doctrine\Sqid;
use App\Repository\Sprint\SprintGoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

#[ORM\Entity(repositoryClass: SprintGoalRepository::class)]
class SprintGoal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column()]
    private ?string $name = null;

    /**
     * @var Collection<int, SprintGoalIssue>
     */
    #[ORM\OneToMany(targetEntity: SprintGoalIssue::class, mappedBy: 'sprintGoal')]
    private Collection $sprintGoalIssues;

    #[ORM\ManyToOne(inversedBy: 'sprintGoals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sprint $sprint = null;

    public function __construct(
        string $name,
        Sprint $sprint
    ) {
        $this->sprintGoalIssues = new ArrayCollection();
        $this->name = $name;
        $this->sprint = $sprint;
    }

    public function getId(): ?Sqid
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

    /**
     * @return Collection<int, SprintGoalIssue>
     */
    public function getSprintGoalIssues(): Collection
    {
        return $this->sprintGoalIssues;
    }

    public function addSprintGoalIssue(SprintGoalIssue $sprintGoalIssue): void
    {
        if ($this->sprintGoalIssues->contains($sprintGoalIssue)) {
            throw new RuntimeException('Sprint goal issue is already added');
        }

        $this->sprintGoalIssues->add($sprintGoalIssue);
    }

    public function removeSprintGoalIssue(SprintGoalIssue $sprintGoalIssue): void
    {
        $this->sprintGoalIssues->removeElement($sprintGoalIssue);
    }

    public function getSprint(): ?Sprint
    {
        return $this->sprint;
    }

    public function setSprint(?Sprint $sprint): static
    {
        $this->sprint = $sprint;

        return $this;
    }
}
