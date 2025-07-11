<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectMemberRole;
use App\Entity\User\User;
use App\Event\Project\Event\AddMemberEvent;
use App\Event\Project\Event\RemoveMemberEvent;
use App\Exception\Project\CannotAddProjectMemberException;
use App\Exception\Project\CannotRemoveProjectMemberException;
use App\Repository\Project\ProjectRoleRepository;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectEditor
{

    public function __construct(
        private Project $project,
        private EntityManagerInterface $entityManager,
        private EventPersister $eventPersister,
        private ProjectRoleRepository $projectRoleRepository,
    ) {
    }

    public function addMemberAdmin(User $newMemberAdmin): void
    {
        $newMemberAdmin = $this->createMember($newMemberAdmin);

        $projectMemberRole = new ProjectMemberRole(
            role: $this->projectRoleRepository->adminRole(),
            projectMember: $newMemberAdmin
        );

        $this->entityManager->persist($projectMemberRole);

        $this->entityManager->flush();

        $this->eventPersister->create(new AddMemberEvent(
            userId: $newMemberAdmin->getUser()->getId()->integerId()
        ));
    }

    public function addMember(User $newMember): void
    {
        $this->createMember($newMember);

        $this->entityManager->flush();

        $this->eventPersister->create(new AddMemberEvent(
            userId: $newMember->getId()->integerId()
        ));
    }

    public function removeMember(ProjectMember $projectMember): void
    {
        $userId = $projectMember->getUser()->getId();

        if ($projectMember->isAdmin() && $this->project->getAdmins()->count() <= 1) {
            throw new CannotRemoveProjectMemberException('Cannot remove last project admin');
        }

        $this->project->getMembers()->removeElement($projectMember);

        $this->entityManager->remove($projectMember);

        $this->entityManager->flush();

        $this->eventPersister->create(new RemoveMemberEvent(
            userId: $userId->integerId()
        ));
    }

    private function createMember(User $newMember): ProjectMember
    {
        $memberAlreadyExist = $this->project->getMembers()
            ->findFirst(fn(int $i, ProjectMember $member) => $member->getUser() === $newMember);

        if ($memberAlreadyExist) {
            throw new CannotAddProjectMemberException('Project member already exists.');
        }

        $projectMember = new ProjectMember(
            project: $this->project,
            user: $newMember
        );

        $this->entityManager->persist($projectMember);

        $this->project->getMembers()->add($projectMember);

        return $projectMember;
    }

}
