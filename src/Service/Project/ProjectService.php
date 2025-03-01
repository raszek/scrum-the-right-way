<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Form\Project\ProjectForm;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProjectEditorFactory $projectEditorFactory,
    ) {
    }

    public function create(ProjectForm $projectForm, User $user): Project
    {
        $createdProject = new Project(
            name: $projectForm->name,
            code: $projectForm->code,
            type: $projectForm->type,
        );

        $this->entityManager->persist($createdProject);
        $projectEditor = $this->projectEditorFactory->create($createdProject, $user);
        $projectEditor->addMember($user);
        $projectEditor->createSprint();

        $this->entityManager->flush();

        return $createdProject;
    }

}
