<?php

namespace App\Tests\Controller;

use App\Enum\Issue\IssueColumnEnum;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\IssueRepository;
use Carbon\CarbonImmutable;
use Zenstruck\Foundry\Test\Factories;

class IssueControllerTest extends WebTestCase
{

    use Factories;

    /** @test */
    public function project_member_can_access_backlog_issues()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        IssueFactory::createOne([
            'title' => 'First issue',
            'number' => 1,
            'project' => $project,
            'issueColumn' => $backlogColumn,
        ]);

        IssueFactory::createOne([
            'title' => 'Second issue',
            'number' => 2,
            'project' => $project,
            'issueColumn' => $backlogColumn,
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/backlog');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('First issue');
        $this->assertResponseHasText('Second issue');
    }

    /** @test */
    public function only_project_members_can_access_project_kanban()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/kanban');

        $this->assertResponseStatusCodeSame(403);
    }

    /** @test */
    public function analytic_can_create_new_issues_on_backlog()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $analyticMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $analyticMember,
            'role' => $analyticRole
        ]);

        IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe('/projects/' . $project->getId() . '/backlog');

        $form = $crawler->selectButton('Create')->form();

        $client->submit($form, [
            'create_issue[title]' => 'First issue with title',
            'create_issue[type]' => $issueType->getId()
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('First issue with title');

        $createdIssue = $this->issueRepository()->findOneBy([
            'title' => 'First issue with title',
        ]);

        $this->assertNotNull($createdIssue);
        $this->assertEquals('SCP-1', $createdIssue->getCode());
        $this->assertEquals(1024, $createdIssue->getColumnOrder());
        $this->assertEquals($createdIssue->getCreatedBy()->getId(), $user->getId());
        $this->assertEquals(IssueColumnEnum::Backlog->value, $createdIssue->getIssueColumn()->getId());
        $this->assertEquals('Issue', $createdIssue->getType()->getLabel());

        $firstIssueObserver = $createdIssue->getObservers()->get(0);
        $this->assertNotNull($firstIssueObserver);
        $this->assertEquals($analyticMember->getId(), $firstIssueObserver->getProjectMember()->getId());
    }

    /** @test */
    public function project_member_can_see_issue_list()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'firstName' => 'Mallie',
            'lastName' => 'Mann',
        ]);

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        IssueFactory::createOne([
            'title' => 'First task',
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
            'description' => 'Some task description',
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12),
            'updatedAt' => CarbonImmutable::create(2013, 12, 13, 13, 13),
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/issues');

        $this->assertResponseIsSuccessful();

        $table = $this->readTable('table');
        
        $firstRecord = $table[1];

        $this->assertEquals([
            'First task',
            'SCP-12',
            'Backlog',
            'Issue',
            'Mallie Mann',
            'December 12, 2012 12:12',
            'December 13, 2013 13:13',
        ], $firstRecord);
    }

    /** @test */
    public function project_member_can_see_issue_details()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        IssueFactory::createOne([
            'title' => 'First task',
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
            'description' => 'Some task description'
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/issues/SCP-12');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('First task');
        $this->assertResponseHasText('Some task description');
    }

    /** @test */

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
