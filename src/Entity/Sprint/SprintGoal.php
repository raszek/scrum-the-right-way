<?php

namespace App\Entity\Sprint;

use App\Repository\Sprint\SprintGoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SprintGoalRepository::class)]
class SprintGoal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
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

    /**
     * @return Collection<int, SprintGoalIssue>
     */
    public function getSprintGoalIssues(): Collection
    {
        return $this->sprintGoalIssues;
    }

    public function addSprintGoalIssue(SprintGoalIssue $sprintGoalIssue): static
    {
        if (!$this->sprintGoalIssues->contains($sprintGoalIssue)) {
            $this->sprintGoalIssues->add($sprintGoalIssue);
            $sprintGoalIssue->setSprintGoal($this);
        }

        return $this;
    }

    public function removeSprintGoalIssue(SprintGoalIssue $sprintGoalIssue): static
    {
        if ($this->sprintGoalIssues->removeElement($sprintGoalIssue)) {
            // set the owning side to null (unless already changed)
            if ($sprintGoalIssue->getSprintGoal() === $this) {
                $sprintGoalIssue->setSprintGoal(null);
            }
        }

        return $this;
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
