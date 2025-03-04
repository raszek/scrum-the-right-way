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
    public function only_features_and_issues_are_visible_on_backlog()
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

        $featureType = IssueTypeFactory::featureType();

        $issueType = IssueTypeFactory::issueType();

        $subIssueType = IssueTypeFactory::subIssueType();

        $feature = IssueFactory::createOne([
            'title' => 'First issue',
            'number' => 1,
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $featureType,
        ]);

        IssueFactory::createOne([
            'title' => 'Sub issue',
            'number' => 2,
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $subIssueType,
            'parent' => $feature,
        ]);

        IssueFactory::createOne([
            'title' => 'Second issue',
            'number' => 3,
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/backlog');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('First issue');
        $this->assertResponseHasText('Second issue');
        $this->assertResponseHasNoText('Sub issue');
    }

    /** @test */
    public function only_project_members_can_access_project_backlog()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/backlog');

        $this->assertResponseStatusCodeSame(403);
    }

    /** @test */
    public function developer_can_create_new_issues_on_backlog()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $developerMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $developerMember,
            'role' => $developerRole
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
        $this->assertEquals($developerMember->getId(), $firstIssueObserver->getProjectMember()->getId());
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
    public function project_member_cannot_see_archived_sub_issues()
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

        $archivedColumn = IssueColumnFactory::archivedColumn();

        $featureType = IssueTypeFactory::featureType();

        $subIssueType = IssueTypeFactory::subIssueType();

        $feature = IssueFactory::createOne([
            'title' => 'First task',
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $featureType,
            'number' => 1,
        ]);

        IssueFactory::createOne([
            'title' => 'Sub issue visible',
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $subIssueType,
            'number' => 2,
            'parent' => $feature
        ]);

        IssueFactory::createOne([
            'title' => 'Sub issue archived',
            'project' => $project,
            'issueColumn' => $archivedColumn,
            'type' => $subIssueType,
            'number' => 3,
            'parent' => $feature
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/issues/SCP-1');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Sub issue visible');
        $this->assertResponseHasNoText('Sub issue archived');
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
