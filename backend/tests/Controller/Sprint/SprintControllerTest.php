<?php

namespace App\Tests\Controller\Sprint;

use App\Entity\Sprint\SprintGoalIssue;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Sprint\SprintGoalFactory;
use App\Factory\Sprint\SprintGoalIssueFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Repository\Sprint\SprintRepository;
use App\Service\Common\ClockInterface;
use App\Tests\Controller\WebTestCase;
use Carbon\CarbonImmutable;

class SprintControllerTest extends WebTestCase
{

    /** @test */
    public function only_features_and_issues_are_visible_on_sprint_page()
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

        $toDoColumn = IssueColumnFactory::todoColumn();

        $issueType = IssueTypeFactory::issueType();

        $featureType = IssueTypeFactory::featureType();

        $subIssueType = IssueTypeFactory::subIssueType();

        $issue = IssueFactory::createOne([
            'title' => 'Issue task',
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'type' => $issueType,
            'number' => 1,
        ]);

        $feature = IssueFactory::createOne([
            'title' => 'Feature task',
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'type' => $featureType,
            'number' => 2,
        ]);

        $subIssue = IssueFactory::createOne([
            'title' => 'Sub issue task',
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'type' => $subIssueType,
            'number' => 3,
            'parent' => $feature,
            'issueOrder' => 128
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'sprint' => $sprint,
            'name' => 'First sprint goal',
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $issue,
            'sprintGoal' => $sprintGoal,
        ]);

        $secondSprintGoal = SprintGoalFactory::createOne([
            'sprint' => $sprint,
            'name' => 'Second sprint goal',
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $feature,
            'sprintGoal' => $secondSprintGoal,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $subIssue,
            'sprintGoal' => $secondSprintGoal,
        ]);

        $this->loginAsUser($user);

        $this->goToPage(sprintf('/projects/%s/sprints/current/plan', $project->getId()));

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Issue task');
        $this->assertResponseHasText('Feature task');
        $this->assertResponseHasText('First sprint goal');
        $this->assertResponseHasText('Second sprint goal');
        $this->assertResponseHasNoText('Sub issue task');
    }


    /** @test */
    public function developer_can_add_sprint_goal()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true,
            'number' => 1
        ]);

        SprintGoalFactory::createOne([
            'name' => 'Some sprint name',
            'sprint' => $sprint
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/plan',
            $project->getId(),
        );

        $crawler = $this->goToPageSafe($uri);

        $form = $crawler->selectButton('Add')->form();

        $client->submit($form, [
            'sprint_goal_form[name]' => 'Another sprint goal'
        ]);

        $this->assertResponseIsSuccessful();

        $createdSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'name' => 'Another sprint goal'
        ]);

        $this->assertNotNull($createdSprintGoal);
    }

    /** @test */
    public function developer_can_start_sprint()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $mockClock = new class implements ClockInterface {
            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2012, 12, 12);
            }
        };

        $this->mockService(ClockInterface::class, $mockClock);

        $developer = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'storyPoints' => 3
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true,
            'number' => 1,
            'startedAt' => null
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Some sprint name',
            'sprint' => $sprint
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $issue,
            'sprintGoal' => $sprintGoal
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/plan',
            $project->getId(),
        );

        $crawler = $this->goToPageSafe($uri);

        $form = $crawler->selectButton('Start sprint')->form();

        $client->submit($form, [
            'start_sprint[estimatedEndDate]' => CarbonImmutable::create(2012, 12, 19)->format('Y-m-d')
        ]);

        $this->assertResponseIsSuccessful();

        $updatedSprint = $this->sprintRepository()->findOneBy([
            'id' => $sprint->getId()
        ]);

        $this->assertNotNull($updatedSprint);
        $this->assertNotNull($updatedSprint->getStartedAt());
    }

    /** @test */
    public function user_can_view_sprint_overview()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $issue = IssueFactory::createOne([
            'title' => 'Super issue',
            'project' => $project,
            'storyPoints' => 3,
            'issueColumn' => IssueColumnFactory::doneColumn(),
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true,
            'number' => 1,
            'startedAt' => CarbonImmutable::create(2012, 12, 12),
            'estimatedEndDate' => CarbonImmutable::create(2012, 12, 19),
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Some sprint name',
            'sprint' => $sprint
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $issue,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => CarbonImmutable::create(2012, 12, 14)
        ]);

        $this->loginAsUser($user);

        $uri = sprintf(
            '/projects/%s/scrum/home',
            $project->getId(),
        );

        $this->goToPage($uri);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Super issue');
    }

    /** @test */
    public function developer_can_finish_sprint()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $issueType = IssueTypeFactory::issueType();
        $featureType = IssueTypeFactory::featureType();
        $subIssueType = IssueTypeFactory::subIssueType();

        $inProgressColumn = IssueColumnFactory::inProgressColumn();
        $doneColumn = IssueColumnFactory::doneColumn();
        $finishedColumn = IssueColumnFactory::finishedColumn();

        $inProgressIssue = IssueFactory::createOne([
            'number' => 1,
            'project' => $project,
            'type' => $issueType,
            'storyPoints' => 5,
            'issueColumn' => $inProgressColumn
        ]);

        $doneIssue = IssueFactory::createOne([
            'number' => 2,
            'project' => $project,
            'type' => $issueType,
            'storyPoints' => 3,
            'issueColumn' => $doneColumn
        ]);

        $doneFeature = IssueFactory::createOne([
            'number' => 3,
            'project' => $project,
            'type' => $featureType,
            'storyPoints' => 5,
            'issueColumn' => $doneColumn
        ]);

        $doneSubIssue = IssueFactory::createOne([
            'number' => 4,
            'project' => $project,
            'type' => $subIssueType,
            'storyPoints' => 5,
            'issueColumn' => $doneColumn,
            'parent' => $doneFeature
        ]);

        $partiallyDoneFeature = IssueFactory::createOne([
            'number' => 5,
            'project' => $project,
            'type' => $featureType,
            'storyPoints' => 8,
            'issueColumn' => $inProgressColumn
        ]);

        $partiallyDoneSubIssue = IssueFactory::createOne([
            'number' => 6,
            'project' => $project,
            'type' => $subIssueType,
            'storyPoints' => 3,
            'issueColumn' => $doneColumn,
            'parent' => $partiallyDoneFeature
        ]);

        $partiallyInProgressSubIssue = IssueFactory::createOne([
            'number' => 7,
            'project' => $project,
            'type' => $subIssueType,
            'storyPoints' => 5,
            'issueColumn' => $inProgressColumn,
            'parent' => $partiallyDoneFeature
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true,
            'number' => 1,
            'startedAt' => CarbonImmutable::create(2012, 12, 12),
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Some sprint name',
            'sprint' => $sprint
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $doneIssue,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => CarbonImmutable::create(2012, 12, 12),
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $inProgressIssue,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => null,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $doneFeature,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => CarbonImmutable::create(2012, 12, 12),
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $doneSubIssue,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => CarbonImmutable::create(2012, 12, 12),
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $partiallyDoneFeature,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => null,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $partiallyDoneSubIssue,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => CarbonImmutable::create(2012, 12, 12),
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $partiallyInProgressSubIssue,
            'sprintGoal' => $sprintGoal,
            'finishedAt' => null
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/finish',
            $project->getId(),
        );

        $client->request('POST', $uri);

        $this->assertResponseIsSuccessful();

        $this->assertFalse($sprint->isCurrent());
        $this->assertNotNull($sprint->getEndedAt());

        $createdSprint = $this->sprintRepository()->findOneBy([
            'project' => $project->getId(),
            'isCurrent' => true,
            'number' => 2
        ]);

        $this->assertNotNull($createdSprint);
        $this->assertCount(1, $createdSprint->getSprintGoals());

        $this->assertTrue($inProgressIssue->getIssueColumn()->isBacklog());
        $this->assertNull($inProgressIssue->getStoryPoints());
        $this->assertEquals(5 ,$inProgressIssue->getPreviousStoryPoints());

        $this->assertTrue($partiallyDoneFeature->getIssueColumn()->isBacklog());;
        $this->assertNull($partiallyDoneFeature->getStoryPoints());
        $this->assertNull($partiallyDoneFeature->getPreviousStoryPoints());

        $this->assertTrue($partiallyInProgressSubIssue->getIssueColumn()->isBacklog());;
        $this->assertNull($partiallyInProgressSubIssue->getStoryPoints());
        $this->assertEquals(5 ,$partiallyInProgressSubIssue->getPreviousStoryPoints());

        $doneIssues = $this->issueRepository()->findBy([
            'id' => [
                $doneIssue->getId()->integerId(),
                $doneFeature->getId()->integerId(),
                $doneSubIssue->getId()->integerId(),
                $partiallyDoneSubIssue->getId()->integerId(),
            ],
            'issueColumn' => $finishedColumn->getId(),
        ]);

        $this->assertCount(4, $doneIssues);

        $sprintHistoryStoryPoints = $sprint->getSprintGoals()
            ->get(0)
            ->getSprintGoalIssues()
            ->map(fn(SprintGoalIssue $sprintGoalIssue) => $sprintGoalIssue->getStoryPoints())
            ->toArray();

        $this->assertEquals([
            3,
            5,
            5,
            5,
            8,
            3,
            5
        ], $sprintHistoryStoryPoints);
    }

    /** @test */
    public function project_member_can_view_list_of_sprints()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        SprintFactory::createOne([
            'project' => $project,
            'number' => 1,
            'startedAt' => CarbonImmutable::create(2012, 12, 12),
            'estimatedEndDate' => CarbonImmutable::create(2012, 12, 19),
        ]);

        SprintFactory::createOne([
            'project' => $project,
            'number' => 2,
            'isCurrent' => true,
            'startedAt' => CarbonImmutable::create(2012, 12, 19),
        ]);

        $this->loginAsUser($user);

        $uri = sprintf(
            '/projects/%s/sprints',
            $project->getId(),
        );

        $this->goToPage($uri);

        $this->assertResponseIsSuccessful();

        $table = $this->readTable('table');

        $this->assertCount(2, $table);

        $this->assertEquals('Sprint 1', $table[1][0]);
    }

    /** @test */
    public function project_member_can_view_sprint()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        SprintFactory::createOne([
            'project' => $project,
            'number' => 2,
            'isCurrent' => true,
        ]);

        $olderSprint = SprintFactory::createOne([
            'project' => $project,
            'number' => 1,
            'startedAt' => CarbonImmutable::create(2012, 12, 12),
            'endedAt' => CarbonImmutable::create(2012, 12, 19),
        ]);

        $sprintGoalOne = SprintGoalFactory::createOne([
            'sprint' => $olderSprint,
            'name' => 'Sprint goal 1',
            'sprintOrder' => 128,
        ]);

        $issue = IssueFactory::createOne([
            'title' => 'Issue task',
            'project' => $project,
            'number' => 1,
            'type' => IssueTypeFactory::issueType(),
            'issueColumn' => IssueColumnFactory::finishedColumn(),
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $issue,
            'sprintGoal' => $sprintGoalOne,
            'finishedAt' => CarbonImmutable::create(2012, 12, 14),
            'goalOrder' => 128,
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $feature = IssueFactory::createOne([
            'title' => 'Feature task',
            'project' => $project,
            'number' => 2,
            'type' => IssueTypeFactory::featureType(),
            'issueColumn' => $backlogColumn,
        ]);

        $subIssue = IssueFactory::createOne([
            'title' => 'Sub issue task',
            'project' => $project,
            'number' => 3,
            'type' => IssueTypeFactory::subIssueType(),
            'issueColumn' => $backlogColumn,
            'parent' => $feature,
        ]);

        $sprintGoalTwo = SprintGoalFactory::createOne([
            'sprint' => $olderSprint,
            'name' => 'Sprint goal 2',
            'sprintOrder' => 256,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $feature,
            'sprintGoal' => $sprintGoalTwo,
            'goalOrder' => 128,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $subIssue,
            'sprintGoal' => $sprintGoalTwo,
            'goalOrder' => 256,
        ]);

        $this->loginAsUser($user);

        $uri = sprintf(
            '/projects/%s/sprints/%s/view',
            $project->getId(),
            $olderSprint->getId()
        );

        $this->goToPage($uri);

        $this->assertResponseIsSuccessful();

        $table = $this->readTable('table');

        $this->assertCount(6, $table);

        $this->assertEquals('Sprint goal 1', $table[1][0]);
        $this->assertEquals('[#1] [Issue] Issue task', $table[2][0]);
        $this->assertEquals('Sprint goal 2', $table[3][0]);
        $this->assertEquals('[#2] [Feature] Feature task', $table[4][0]);
        $this->assertEquals('[#3] [Sub issue] Sub issue task', $table[5][0]);
    }

    private function sprintGoalRepository(): SprintGoalRepository
    {
        return $this->getService(SprintGoalRepository::class);
    }

    private function sprintRepository(): SprintRepository
    {
        return $this->getService(SprintRepository::class);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
