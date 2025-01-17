<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    public function create(Project $project, User $user): ProjectEditor
    {
        return new ProjectEditor(
            project: $project,
            entityManager: $this->entityManager,
            eventPersister: $this->eventPersisterFactory->create($project, $user)
        );
    }
}
