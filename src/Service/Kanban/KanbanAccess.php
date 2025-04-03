<?php

namespace App\Service\Kanban;

use App\Entity\Project\Project;
use App\Repository\Sprint\SprintRepository;

readonly class KanbanAccess
{

    public function __construct(
        private SprintRepository $sprintRepository,
    ) {
    }

    public function kanbanViewAccessError(Project $project): ?string
    {
        if ($project->isKanban()) {
            return null;
        }

        $currentSprint = $this->sprintRepository->getCurrentSprint($project);

        if (!$currentSprint->isStarted()) {
            return 'Cannot access kanban view. Kanban view is accessible when sprint has been started.';
        }

        return null;
    }

    public function isKanbanViewAccessible(Project $project): bool
    {
        return $this->kanbanViewAccessError($project) === null;
    }
}
