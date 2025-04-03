<?php

namespace App\Service\Sprint;

use App\Entity\Project\Project;
use App\Repository\Sprint\SprintRepository;

readonly class SprintAccess
{

    public function __construct(
        private SprintRepository $sprintRepository,
    ) {
    }

    public function sprintViewAccessError(Project $project): ?string
    {
        if ($project->isKanban()) {
            return 'Cannot access sprint view. Kanban project cannot access sprint view';
        }

        $currentSprint = $this->sprintRepository->getCurrentSprint($project);
        if ($currentSprint->isStarted()) {
            return 'Cannot access sprint view. Kanban project cannot access sprint view';
        }

        return null;
    }

    public function isSprintViewAccessible(Project $project): bool
    {
        return $this->sprintViewAccessError($project) === null;
    }
}
