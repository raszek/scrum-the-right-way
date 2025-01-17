<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectMemberRole;
use App\Entity\Project\ProjectRole;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Event\Project\Event\AddRoleEvent;
use App\Event\Project\Event\RemoveRoleEvent;
use App\Exception\Project\ProjectMemberCannotAddRoleException;
use App\Exception\Project\ProjectMemberCannotRemoveRoleException;
use App\Repository\Project\ProjectRoleRepository;
use App\Service\Event\EventPersister;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class ProjectMemberEditor
{
    private EventPersister $eventPersister;

    public function __construct(
        private ProjectMember $projectMember,
        private User $user,
        private ProjectRoleRepository $projectRoleRepository,
        private EntityManagerInterface $entityManager,
        Project $project,
        EventPersisterFactory $eventPersisterFactory,
    ) {
        $this->eventPersister = $eventPersisterFactory->create($project, $this->user);
    }

    public function addRole(ProjectRoleEnum $role): void
    {
        $projectMember = $this->projectMember;

        if ($projectMember->hasRole($role)) {
            throw new ProjectMemberCannotAddRoleException(sprintf(
                'Project member already has role "%s"',
                $role->label()
            ));
        }

        if ($projectMember->getProject()->isKanban() && !ProjectRoleEnum::isKanbanRole($role)) {
            throw new ProjectMemberCannotAddRoleException(sprintf(
                'Cannot add role "%s" in kanban project',
                $role->label()
            ));
        }

        $roleToAdd = $this->findRole($role->value);

        $projectMemberRole = new ProjectMemberRole(
            role: $roleToAdd,
            projectMember: $this->projectMember
        );

        $this->entityManager->persist($projectMemberRole);

        $this->projectMember->addRole($projectMemberRole);

        $this->entityManager->flush();

        $this->eventPersister->create(new AddRoleEvent(
            userId: $this->projectMember->getUser()->getId(),
            projectRole: $role->value
        ));
    }

    public function removeRole(ProjectRoleEnum $role): void
    {
        $projectMember = $this->projectMember;

        if (!$projectMember->hasRole($role)) {
            throw new ProjectMemberCannotRemoveRoleException(sprintf(
                'Project member does not have role "%s"',
                $role->label()
            ));
        }

        $this->guardAgainstRemovingAdminRole($role);

        $roleToBeRemoved = $projectMember
            ->getRoles()
            ->findFirst(fn(int $i, ProjectMemberRole $projectMemberRole) => $projectMemberRole->isRole($role));

        $projectMember->removeRole($roleToBeRemoved);

        $this->entityManager->remove($roleToBeRemoved);

        $this->entityManager->flush();

        $this->eventPersister->create(new RemoveRoleEvent(
            $projectMember->getUser()->getId(),
            $role->value
        ));

        $this->entityManager->flush();
    }

    private function findRole(int $id): ProjectRole
    {
        $role = $this->projectRoleRepository->findOneBy([
            'id' => $id
        ]);

        if (!$role) {
            throw new Exception('Role not found');
        }

        return $role;
    }

    private function guardAgainstRemovingAdminRole(ProjectRoleEnum $role): void
    {
        if ($role !== ProjectRoleEnum::Admin) {
            return;
        }

        if ($this->projectMember->getUser()->getId() === $this->user->getId()) {
            throw new ProjectMemberCannotRemoveRoleException('You cannot remove admin privileges from yourself. Another project admin has to do this.');
        }
    }

}
