<?php

namespace App\Entity\Sprint;

use App\Entity\Issue\Issue;
use App\Repository\Sprint\SprintGoalIssueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SprintGoalIssueRepository::class)]
class SprintGoalIssue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sprintGoalIssues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SprintGoal $sprintGoal;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue;

    public function __construct(
        SprintGoal $sprintGoal,
        Issue $issue,
    ) {
        $this->sprintGoal = $sprintGoal;
        $this->issue = $issue;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSprintGoal(): ?SprintGoal
    {
        return $this->sprintGoal;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }
}
