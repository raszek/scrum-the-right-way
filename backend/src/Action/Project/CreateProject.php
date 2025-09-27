<?php

namespace App\Action\Project;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Enum\Project\ProjectTypeEnum;
use App\Form\Project\ProjectFormData;
use App\Repository\Project\ProjectTypeRepository;
use App\Service\Project\ProjectEditorFactory;
use App\Service\Sprint\SprintService;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateProject
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SprintService $sprintService,
        private ProjectEditorFactory $projectEditorFactory,
        private ProjectTypeRepository $projectTypeRepository,
    ) {
    }

    public function execute(ProjectFormData $projectForm, User $user): Project
    {
        $projectTypeEnum = ProjectTypeEnum::fromKey($projectForm->type);

        $projectType = $this->projectTypeRepository->getReference($projectTypeEnum);

        $createdProject = new Project(
            name: $projectForm->name,
            code: $projectForm->code,
            type: $projectType,
        );

        $this->entityManager->persist($createdProject);
        $projectEditor = $this->projectEditorFactory->create($createdProject, $user);
        $projectEditor->addMemberAdmin($user);
        $this->sprintService->createSprint($createdProject);

        $this->entityManager->flush();

        return $createdProject;

    }

}
