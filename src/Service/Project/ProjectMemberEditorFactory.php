<?php

namespace App\Service\Project;

use App\Entity\Project\ProjectMember;
use App\Entity\User\User;
use App\Repository\Project\ProjectRoleRepository;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectMemberEditorFactory
{

    public function __construct(
        private ProjectRoleRepository $projectRoleRepository,
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    public function create(ProjectMember $projectMember, User $user): ProjectMemberEditor
    {
        return new ProjectMemberEditor(
            projectMember: $projectMember,
            user: $user,
            projectRoleRepository: $this->projectRoleRepository,
            entityManager: $this->entityManager,
            eventPersister: $this->eventPersisterFactory->create($projectMember->getProject(), $user)
        );
    }
}
