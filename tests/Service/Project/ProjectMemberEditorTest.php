<?php

namespace App\Tests\Service\Project;


use App\Entity\Project\ProjectMember;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Exception\Project\ProjectMemberCannotAddRoleException;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Service\Project\ProjectMemberEditor;
use App\Service\Project\ProjectMemberEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ProjectMemberEditorTest extends KernelTestCase
{



    /** @test */
    public function cannot_add_role_to_project_member_which_already_have_this_role()
    {
        self::bootKernel();

        $admin = UserFactory::createOne();

        $developerRole = ProjectRoleFactory::developerRole();

        $projectMember = ProjectMemberFactory::createOne();

        ProjectMemberRoleFactory::createOne([
            'role' => $developerRole,
            'projectMember' => $projectMember
        ]);

        $editor = $this->create($projectMember, $admin);

        $errorMessage = null;
        try {
            $editor->addRole(ProjectRoleEnum::Developer);
        } catch (ProjectMemberCannotAddRoleException $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertNotNull($errorMessage);
        $this->assertEquals('Project member already has role "Developer"', $errorMessage);
    }

    private function create(ProjectMember $projectMember, User $user): ProjectMemberEditor
    {
        /**
         * @var ProjectMemberEditorFactory $editor
         */
        $editor = $this->getService(ProjectMemberEditorFactory::class);

        return $editor->create($projectMember, $user);
    }
}
