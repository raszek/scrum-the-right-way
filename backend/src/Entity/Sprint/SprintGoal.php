<?php

namespace App\Entity\Sprint;

use App\Doctrine\Sqid;
use App\Repository\Sprint\SprintGoalRepository;
use App\Service\Position\Positionable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

#[ORM\Entity(repositoryClass: SprintGoalRepository::class)]
class SprintGoal implements Positionable
{

    const DEFAULT_ORDER_SPACE = 1024;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column()]
    private ?string $name = null;

    #[ORM\Column()]
    private ?int $sprintOrder;

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
        int $sprintOrder,
        Sprint $sprint
    ) {
        $this->sprintGoalIssues = new ArrayCollection();
        $this->name = $name;
        $this->sprint = $sprint;
        $this->sprintOrder = $sprintOrder;
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

    public function removeErrors(): array
    {
        $errors = [];
        if ($this->sprint->getSprintGoals()->count() <= 1) {
            $errors[] = 'Sprint must have at least one sprint goal';
        }

        return $errors;
    }

    public function canBeRemoved(): bool
    {
        return empty($this->removeErrors());
    }

    public function getSprintOrder(): ?int
    {
        return $this->sprintOrder;
    }

    public function getOrder(): int
    {
        return $this->sprintOrder;
    }

    public function setOrder(int $order): void
    {
        $this->sprintOrder = $order;
    }

    public function getOrderSpace(): int
    {
        return self::DEFAULT_ORDER_SPACE;
    }
}
