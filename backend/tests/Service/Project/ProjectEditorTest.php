<?php

namespace App\Tests\Service\Project;


use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Exception\Project\CannotRemoveProjectMemberException;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Service\Project\ProjectEditor;
use App\Service\Project\ProjectEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ProjectEditorTest extends KernelTestCase
{



    /** @test */
    public function member_admin_cannot_remove_yourself_from_project()
    {
        self::bootKernel();

        $project = ProjectFactory::createOne();

        $admin = UserFactory::createOne();

        $adminMember = ProjectMemberFactory::createOne([
            'project' => $project,
            'user' => $admin
        ]);

        $adminRole = ProjectRoleFactory::adminRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $adminMember,
            'role' => $adminRole
        ]);

        $projectEditor = $this->create($project, $admin);


        $errorMessage = null;
        try {
            $projectEditor->removeMember($adminMember);
        } catch (CannotRemoveProjectMemberException $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertNotNull($errorMessage);
        $this->assertEquals('Cannot remove last project admin', $errorMessage);
    }

    private function create(Project $project, User $user): ProjectEditor
    {
        return $this->getService(ProjectEditorFactory::class)->create($project, $user);
    }

}
