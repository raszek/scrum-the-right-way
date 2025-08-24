<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Repository\Sprint\SprintRepository;

readonly class StoryPointService
{

    public function __construct(
        private SprintRepository $sprintRepository,
    ) {
    }

    /**
     * @return int[]
     */
    public function recommendedStoryPoints(): array
    {
        return [
            1,
            2,
            3,
            5,
            8,
            13,
            20,
            40,
            100
        ];
    }

    public function isPreviousStoryPointsVisible(Issue $issue): bool
    {
        if ($issue->getPreviousStoryPoints() === null) {
            return false;
        }

        $column = $issue->getIssueColumn();

        if ($column->isBacklog()) {
            return true;
        }

        $currentSprint = $this->sprintRepository->getCurrentSprint($issue->getProject());

        return $column->isToDo() && !$currentSprint->isStarted();
    }

}
