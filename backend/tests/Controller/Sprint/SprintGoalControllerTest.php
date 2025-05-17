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
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Tests\Controller\WebTestCase;

class SprintGoalControllerTest extends WebTestCase
{
    /** @test */
    public function developer_can_edit_sprint_goal_text()
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

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Some sprint goal',
            'sprint' => $sprint
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/goals/%s/edit',
            $project->getId(),
            $sprintGoal->getId()
        );

        $client->request('POST', $uri, [
            'name' => 'New name for sprint goal'
        ]);

        $this->assertResponseIsSuccessful();

        $changedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoal->getId()
        ]);

        $this->assertNotNull($changedSprintGoal);
        $this->assertEquals('New name for sprint goal', $changedSprintGoal->getName());
    }

    /** @test */
    public function developer_can_remove_sprint_goal()
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

        IssueColumnFactory::backlogColumn();
        $todoColumn = IssueColumnFactory::todoColumn();

        $issue = IssueFactory::createOne([
            'number' => 1,
            'project' => $project,
            'issueColumn' => $todoColumn,
            'type' => $issueType,
        ]);

        $feature = IssueFactory::createOne([
            'number' => 2,
            'project' => $project,
            'issueColumn' => $todoColumn,
            'type' => $featureType,
        ]);

        $subIssue = IssueFactory::createOne([
            'number' => 3,
            'project' => $project,
            'issueColumn' => $todoColumn,
            'type' => $subIssueType,
            'parent' => $feature
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true,
            'number' => 1
        ]);

        SprintGoalFactory::createOne([
            'name' => 'Some sprint goal',
            'sprint' => $sprint
        ]);

        $sprintGoalToBeRemoved = SprintGoalFactory::createOne([
            'name' => 'Another sprint goal',
            'sprint' => $sprint
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $issue,
            'sprintGoal' => $sprintGoalToBeRemoved,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $feature,
            'sprintGoal' => $sprintGoalToBeRemoved,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $subIssue,
            'sprintGoal' => $sprintGoalToBeRemoved,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/goals/%s/remove',
            $project->getId(),
            $sprintGoalToBeRemoved->getId()
        );

        $client->request('POST', $uri);

        $this->assertResponseIsSuccessful();

        $removedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoalToBeRemoved->getId()
        ]);

        $this->assertNull($removedSprintGoal);

        $this->assertCount(0, $this->sprintGoalIssueRepository()->findAll());

        $this->assertTrue($issue->getIssueColumn()->isBacklog());
        $this->assertTrue($feature->getIssueColumn()->isBacklog());
        $this->assertTrue($subIssue->getIssueColumn()->isBacklog());
    }

    /** @test */
    public function developer_can_move_sprint_goal()
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
            'name' => 'First sprint goal',
            'sprint' => $sprint,
            'sprintOrder' => 1024
        ]);

        $secondSprintGoal = SprintGoalFactory::createOne([
            'name' => 'Second sprint goal',
            'sprint' => $sprint,
            'sprintOrder' => 2048
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/goals/%s/sort',
            $project->getId(),
            $secondSprintGoal->getId()
        );

        $client->request('POST', $uri, [
            'position' => 1
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $secondSprintGoal->getId()
        ]);

        $this->assertNotNull($updatedSprintGoal);
        $this->assertEquals(512, $updatedSprintGoal->getSprintOrder());
    }

    private function sprintGoalRepository(): SprintGoalRepository
    {
        return $this->getService(SprintGoalRepository::class);
    }

    private function sprintGoalIssueRepository(): SprintGoalIssueRepository
    {
        return $this->getService(SprintGoalIssueRepository::class);
    }
}
