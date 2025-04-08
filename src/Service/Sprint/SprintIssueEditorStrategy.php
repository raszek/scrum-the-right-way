<?php

namespace App\Service\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Sprint\SprintGoalIssue;
use App\Enum\Issue\IssueColumnEnum;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Issue\IssueEditor\ProjectIssueEditorStrategy;
use RuntimeException;

readonly class SprintIssueEditorStrategy implements ProjectIssueEditorStrategy
{

    public function __construct(
        private Issue $issue,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
        private ClockInterface $clock,
    ) {
    }

    public function changeKanbanColumn(IssueColumnEnum $column): void
    {
        if (!$this->issue->getIssueColumn()->isDone() && $column === IssueColumnEnum::Done) {
            $this->findSprintGoalIssue()->setFinishedAt($this->clock->now());
        } else if ($this->issue->getIssueColumn()->isDone() && $column !== IssueColumnEnum::Done) {
            $this->findSprintGoalIssue()->setFinishedAt(null);
        }
    }

    private function findSprintGoalIssue(): SprintGoalIssue
    {
        $sprintGoalIssue =  $this->sprintGoalIssueRepository->findCurrentSprintIssue($this->issue);

        if (!$sprintGoalIssue) {
            throw new RuntimeException('Sprint goal issue not found on sprint');
        }

        return $sprintGoalIssue;
    }
}
