<?php

namespace App\Tests\Controller\Sprint;

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

        $this->goToPage(sprintf('/projects/%s/sprints/current', $project->getId()));

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Issue task');
        $this->assertResponseHasText('Feature task');
        $this->assertResponseHasText('First sprint goal');
        $this->assertResponseHasText('Second sprint goal');
        $this->assertResponseHasNoText('Sub issue task');
    }

    /** @test */
    public function developer_can_add_issue_to_sprint_in_first_sprint_goal_only()
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

        $backlogColumn = IssueColumnFactory::backlogColumn();
        IssueColumnFactory::todoColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'sprint' => $sprint,
        ]);

        $anotherSprintGoal = SprintGoalFactory::createOne([
            'sprint' => $sprint,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/issues/SCP-12',
            $project->getId(),
        );

        $client->request('POST', $uri);

        $this->assertResponseIsSuccessful();

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertTrue($updatedIssue->getIssueColumn()->isToDo());

        $updatedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoal->getId()
        ]);

        $this->assertEquals(1, $updatedSprintGoal->getSprintGoalIssues()->count());

        $notUpdatedAnotherSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $anotherSprintGoal->getId()
        ]);

        $this->assertEquals(0, $notUpdatedAnotherSprintGoal->getSprintGoalIssues()->count());
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
            '/projects/%s/sprints/current',
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
            '/projects/%s/sprints/current',
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
