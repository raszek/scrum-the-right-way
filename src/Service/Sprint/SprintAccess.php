<?php

namespace App\Service\Sprint;

use App\Entity\Project\Project;

readonly class SprintAccess
{

    public function sprintViewAccessError(Project $project): ?string
    {
        if ($project->isKanban()) {
            return 'Cannot access sprint view. Kanban project cannot access sprint view';
        }

        return null;
    }

    public function isSprintViewAccessible(Project $project): bool
    {
        return $this->sprintViewAccessError($project) === null;
    }
}
