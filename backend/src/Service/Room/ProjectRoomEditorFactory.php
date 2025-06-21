<?php

namespace App\Service\Room;

use App\Entity\Project\Project;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectRoomEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {
    }

    public function create(Project $project): ProjectRoomEditor
    {
        return new ProjectRoomEditor(
            project: $project,
            entityManager: $this->entityManager,
            clock: $this->clock,
        );
    }
}
