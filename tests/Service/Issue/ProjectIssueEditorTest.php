<?php

namespace App\Tests\Service\Issue;


use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\UserFactory;
use App\Form\Issue\CreateIssueForm;
use App\Repository\Issue\IssueRepository;
use App\Service\Issue\ProjectIssueEditor;
use App\Service\Issue\ProjectIssueEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ProjectIssueEditorTest extends KernelTestCase
{

    use Factories;

    /** @test */
    public function issues_will_be_defragmented_when_no_space_on_first_position()
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $member = ProjectMemberFactory::createOne([
            'project' => $project,
            'user' => $user
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'columnOrder' => 1,
            'title' => 'some task',
            'issueColumn' => $backlogColumn
        ]);

        $projectIssueEditor = $this->create($project->_real(), $member->_real());

        $projectIssueEditor->createIssue(new CreateIssueForm(
            title: 'issue',
            type: $issueType->_real()
        ));

        $createdIssue = $this->issueRepository()->findOneBy([
            'title' => 'issue'
        ]);

        $this->assertNotNull($createdIssue);
        $this->assertEquals(2, $createdIssue->getNumber());
        $this->assertEquals(512, $createdIssue->getColumnOrder());
    }

    /** @test */
    public function created_issues_are_inserted_between_0_and_first_issue_column_order()
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $member = ProjectMemberFactory::createOne([
            'project' => $project,
            'user' => $user
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'columnOrder' => 512,
            'title' => 'some task',
            'issueColumn' => $backlogColumn
        ]);

        $projectIssueEditor = $this->create($project->_real(), $member->_real());

        $projectIssueEditor->createIssue(new CreateIssueForm(
            title: 'issue',
            type: $issueType->_real()
        ));

        $createdIssue = $this->issueRepository()->findOneBy([
            'title' => 'issue'
        ]);

        $this->assertNotNull($createdIssue);
        $this->assertEquals(2, $createdIssue->getNumber());
        $this->assertEquals(256, $createdIssue->getColumnOrder());
    }

    private function create(Project $project, ProjectMember $member): ProjectIssueEditor
    {
        return $this->getService(ProjectIssueEditorFactory::class)->create($project, $member);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

}
