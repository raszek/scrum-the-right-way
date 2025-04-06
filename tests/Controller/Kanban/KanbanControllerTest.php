<?php

namespace App\Tests\Controller\Kanban;

use App\Enum\Issue\IssueColumnEnum;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\IssueRepository;
use App\Tests\Controller\WebTestCase;
use Carbon\CarbonImmutable;

class KanbanControllerTest extends WebTestCase
{

    /** @test */
    public function project_member_cannot_see_kanban_when_sprint_is_not_started()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create();

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project,
            'startedAt' => null
        ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/kanban');

        $this->assertResponseStatusCodeSame(400);
    }

    /** @test */
    public function project_member_by_default_see_only_sub_issues_and_issues_on_kanban()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project,
            'startedAt' => CarbonImmutable::now()
        ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $toDoColumn = IssueColumnFactory::todoColumn();

        $issueType = IssueTypeFactory::issueType();
        $featureType = IssueTypeFactory::featureType();
        $subIssueType = IssueTypeFactory::subIssueType();

        IssueFactory::createOne([
            'title' => 'Issue title',
            'project' => $project,
            'number' => 1,
            'issueColumn' => $toDoColumn,
            'type' => $issueType,
        ]);

        $feature = IssueFactory::createOne([
            'title' => 'Feature title',
            'project' => $project,
            'number' => 2,
            'issueColumn' => $toDoColumn,
            'type' => $featureType
        ]);

        IssueFactory::createOne([
            'title' => 'Sub issue title',
            'project' => $project,
            'number' => 3,
            'issueColumn' => $toDoColumn,
            'type' => $subIssueType,
            'parent' => $feature,
            'issueOrder' => 128
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/projects/' . $project->getId() . '/kanban');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('SCP-1');
        $this->assertResponseHasText('Issue title');
        $this->assertResponseHasText('SCP-3');
        $this->assertResponseHasText('Sub issue title');

        $this->assertResponseHasNoText('SCP-2');
        $this->assertResponseHasNoText('Feature title');
    }

    /** @test */
    public function developer_can_move_issues_and_between_columns()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project,
            'startedAt' => CarbonImmutable::now()
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

        $toDoColumn = IssueColumnFactory::todoColumn();
        IssueColumnFactory::inProgressColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'title' => 'Issue title',
            'project' => $project,
            'number' => 1,
            'issueColumn' => $toDoColumn,
            'type' => $issueType,
        ]);

        $this->loginAsUser($user);

        $url = sprintf(
            '/projects/%s/kanban/issues/%s/move',
            $project->getId(),
            $issue->getCode()
        );

        $client->request('POST', $url, [
            'position' => 1,
            'column' => 'in-progress'
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertNotNull($updatedIssue);
        $this->assertEquals(IssueColumnEnum::InProgress->value, $updatedIssue->getIssueColumn()->getId());
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
