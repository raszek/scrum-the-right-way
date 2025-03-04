<?php

namespace App\Service\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueColumn;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoalIssue;
use App\Repository\Issue\IssueColumnRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class SprintEditor
{

    public function __construct(
        private Sprint $sprint,
        private EntityManagerInterface $entityManager,
        private IssueColumnRepository $issueColumnRepository,
    ) {
    }

    public function addSprintIssue(Issue $issue): void
    {
        if ($issue->getProject()->getId() !== $this->sprint->getProject()->getId()) {
            throw new RuntimeException('Issue is from other project');
        }

        if (!$this->sprint->isCurrent()) {
            throw new RuntimeException('Issue can be added only to current sprint');
        }

        $firstSprintGoal = $this->sprint->getSprintGoals()->get(0);

        if (!$firstSprintGoal) {
            throw new RuntimeException('Sprint must have at least one sprint goal');
        }

        $sprintGoalIssue = new SprintGoalIssue(
            sprintGoal: $firstSprintGoal,
            issue: $issue,
        );

        $issue->setIssueColumn($this->issueColumnRepository->toDoColumn());

        $this->entityManager->persist($sprintGoalIssue);

        $firstSprintGoal->addSprintGoalIssue($sprintGoalIssue);

        $this->entityManager->flush();
    }

}
