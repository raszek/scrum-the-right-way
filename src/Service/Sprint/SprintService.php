<?php

namespace App\Service\Sprint;


use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;

readonly class SprintService
{

    public function __construct(
        private SprintGoalRepository $sprintGoalRepository,
        private SprintGoalIssueRepository $sprintGoalIssueRepository
    ) {
    }

    /**
     * @param Sprint $sprint
     * @return SprintGoal[]
     */
    public function getSprintGoals(Sprint $sprint): array
    {
        return $this->sprintGoalRepository->getSprintGoals($sprint);
    }

    public function getLatestDoneIssues(Sprint $sprint): array
    {
        return $this->sprintGoalIssueRepository->getLatestDoneIssues($sprint);
    }

}
