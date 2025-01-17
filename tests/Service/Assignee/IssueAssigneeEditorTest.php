<?php

namespace App\Tests\Service\Assignee;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Exception\Assignee\CannotSetAssigneeException;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Service\Assignee\IssueAssigneeEditor;
use App\Service\Assignee\IssueAssigneeEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class IssueAssigneeEditorTest extends KernelTestCase
{
    
    use Factories;

    /** @test */
    public function todo_issue_cannot_bet_set_to_other_assignee_than_developer()
    {
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne();

        $projectMember = ProjectMemberFactory::createOne([
            'project' => $project,
            'user' => $user
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $projectMember,
            'role' => $analyticRole
        ]);

        $toDoColumn = IssueColumnFactory::todoColumn();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $toDoColumn
        ]);

        $issueEditor = $this->create($issue->_real(), $user->_real());

        $errorMessage = null;
        try {
            $issueEditor->setAssignee($projectMember->_real());
        } catch (CannotSetAssigneeException $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertNotNull($errorMessage);
        $this->assertEquals('Cannot set assignee to non developer in [to do, in progress] column', $errorMessage);
    }

    /** @test */
    public function test_issue_cannot_be_assigned_to_other_role_than_tester()
    {
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne();

        $projectMember = ProjectMemberFactory::createOne([
            'project' => $project,
            'user' => $user
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $projectMember,
            'role' => $developerRole
        ]);

        $testColumn = IssueColumnFactory::testColumn();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $testColumn
        ]);

        $issueEditor = $this->create($issue->_real(), $user->_real());

        $errorMessage = null;
        try {
            $issueEditor->setAssignee($projectMember->_real());
        } catch (CannotSetAssigneeException $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertNotNull($errorMessage);
        $this->assertEquals('Cannot set assignee to non tester in [test, tested] column', $errorMessage);
    }

    private function create(Issue $issue, User $user): IssueAssigneeEditor
    {
        return $this->getService(IssueAssigneeEditorFactory::class)->create($issue, $user);
    }

}
