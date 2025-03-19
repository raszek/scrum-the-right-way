<?php

namespace App\Entity\Sprint;

use App\Doctrine\Sqid;
use App\Entity\Issue\Issue;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Position\Positionable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SprintGoalIssueRepository::class)]
class SprintGoalIssue implements Positionable
{
    const DEFAULT_ORDER_SPACE = 1024;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column]
    private ?int $goalOrder = null;

    #[ORM\ManyToOne(inversedBy: 'sprintGoalIssues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SprintGoal $sprintGoal;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue;

    public function __construct(
        SprintGoal $sprintGoal,
        Issue $issue,
        int $goalOrder
    ) {
        $this->sprintGoal = $sprintGoal;
        $this->issue = $issue;
        $this->goalOrder = $goalOrder;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getSprintGoal(): ?SprintGoal
    {
        return $this->sprintGoal;
    }

    public function setSprintGoal(SprintGoal $sprintGoal): void
    {
        $this->sprintGoal = $sprintGoal;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function getOrder(): int
    {
        return $this->goalOrder;
    }

    public function setOrder(int $order): void
    {
        $this->goalOrder = $order;
    }

    public function getOrderSpace(): int
    {
        return self::DEFAULT_ORDER_SPACE;
    }
}
