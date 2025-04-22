<?php

namespace App\Service\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectMemberRole;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Entity\User\User;
use App\Event\Project\Event\AddMemberEvent;
use App\Event\Project\Event\RemoveMemberEvent;
use App\Exception\Project\CannotAddProjectMemberException;
use App\Exception\Project\CannotRemoveProjectMemberException;
use App\Repository\Project\ProjectMemberRoleRepository;
use App\Repository\Project\ProjectRoleRepository;
use App\Repository\Sprint\SprintRepository;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectEditor
{

    public function __construct(
        private Project $project,
        private EntityManagerInterface $entityManager,
        private EventPersister $eventPersister,
        private SprintRepository $sprintRepository,
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
            userId: $newMemberAdmin->getUser()->getId()
        ));
    }

    public function addMember(User $newMember): void
    {
        $this->createMember($newMember);

        $this->entityManager->flush();

        $this->eventPersister->create(new AddMemberEvent(
            userId: $newMember->getId()
        ));
    }

    public function createSprint(): void
    {
        $nextSprintNumber = $this->sprintRepository->getNextSprintNumber($this->project);

        $sprint = new Sprint(
            number: $nextSprintNumber,
            isCurrent: true,
            project: $this->project
        );

        $sprintGoal = new SprintGoal(
            name: 'Define your sprint goal',
            sprintOrder: SprintGoal::DEFAULT_ORDER_SPACE,
            sprint: $sprint
        );

        $this->entityManager->persist($sprint);
        $this->entityManager->persist($sprintGoal);

        $this->entityManager->flush();
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
