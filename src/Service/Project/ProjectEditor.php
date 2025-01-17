<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\User\User;
use App\Event\Project\Event\AddMemberEvent;
use App\Event\Project\Event\RemoveMemberEvent;
use App\Exception\Project\CannotAddProjectMemberException;
use App\Exception\Project\CannotRemoveProjectMemberException;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectEditor
{

    public function __construct(
        private Project $project,
        private EntityManagerInterface $entityManager,
        private EventPersister $eventPersister
    ) {
    }

    public function addMember(User $newMember): void
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

        $this->entityManager->flush();

        $this->eventPersister->create(new AddMemberEvent(
            userId: $newMember->getId()
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
            userId: $userId
        ));
    }

}
