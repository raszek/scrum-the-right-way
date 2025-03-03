<?php

namespace App\DataFixtures;

use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectRole;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Repository\Project\ProjectRoleRepository;
use App\Repository\Project\ProjectTypeRepository;
use App\Repository\User\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProjectTypeRepository $projectTypeRepository,
        private readonly ProjectRoleRepository $projectRoleRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();

        $scrumProject = ProjectFactory::createOne([
            'name' => 'scrum project',
            'code' => 'SCP',
            'type' => $this->projectTypeRepository->findScrum()
        ]);

        $kanbanProject = ProjectFactory::createOne([
            'name' => 'kanban project',
            'code' => 'KBP',
            'type' => $this->projectTypeRepository->findKanban()
        ]);

        foreach ($users as $user) {
            $scrumMember = ProjectMemberFactory::createOne([
                'user' => $user,
                'project' => $scrumProject
            ]);

            $this->assignRole($scrumMember);

            $kanbanMember = ProjectMemberFactory::createOne([
                'user' => $user,
                'project' => $kanbanProject
            ]);

            $this->assignRole($kanbanMember);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProjectTypeFixtures::class,
            ProjectRoleFixtures::class,
        ];
    }

    private function assignRole(ProjectMember $member): void
    {
        $role = $this->findRole($member);

        if (!$role) {
            return;
        }

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $member,
            'role' => $role
        ]);
    }

    private function findRole(ProjectMember $member): ?ProjectRole
    {
        $user = $member->getUser();

        return match ($user->getEmail()) {
            UserFixtures::PROJECT_ADMIN_EMAIL => $this->projectRoleRepository->adminRole(),
            UserFixtures::ANALYTIC_EMAIL, UserFixtures::TESTER_EMAIL, UserFixtures::DEVELOPER_EMAIL => $this->projectRoleRepository->developerRole(),
            default => null
        };
    }
}
