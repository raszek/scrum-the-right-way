<?php

namespace App\Service\Tag;

use App\Entity\Project\Project;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectTagEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(Project $project): ProjectTagEditor
    {
        return new ProjectTagEditor(
            project: $project,
            entityManager: $this->entityManager
        );
    }

}
