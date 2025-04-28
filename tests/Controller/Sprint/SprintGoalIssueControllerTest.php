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
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Tests\Controller\WebTestCase;

class SprintGoalIssueControllerTest extends WebTestCase
{

    /** @test */
    public function developer_can_move_sprint_issue_to_another_goal()
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

        SprintGoalIssueFactory::createOne([
            'sprintGoal' => $sprintGoal,
            'issue' => $issue,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/issues/SCP-12/move',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'position' => 1,
            'goalId' => $anotherSprintGoal->getId()
        ]);

        $this->assertResponseStatusCodeSame(204);

        $previousSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoal->getId()
        ]);

        $this->assertCount(0, $previousSprintGoal->getSprintGoalIssues());

        $updatedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $anotherSprintGoal->getId()
        ]);

        $this->assertCount(1, $updatedSprintGoal->getSprintGoalIssues());

        $updatedSprintGoalIssue = $updatedSprintGoal->getSprintGoalIssues()->first();

        $this->assertEquals(1024, $updatedSprintGoalIssue->getOrder());
    }

    /** @test */
    public function developer_can_remove_issue_from_the_sprint()
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

        SprintGoalIssueFactory::createOne([
            'sprintGoal' => $sprintGoal,
            'issue' => $issue,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/issues/SCP-12/remove',
            $project->getId(),
        );

        $client->request('POST', $uri);

        $this->assertResponseIsSuccessful();

        $updatedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoal->getId()
        ]);

        $this->assertEquals(0, $updatedSprintGoal->getSprintGoalIssues()->count());

        $updatedIssue = $this->issueRepository()->findByCode('SCP-12', $project);

        $this->assertTrue($updatedIssue->getIssueColumn()->isBacklog());
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
            '/projects/%s/sprints/current/issues',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'issueIds' => [
                $issue->getId()
            ]
        ]);

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
    public function developer_can_batch_add_issues_to_sprint()
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
            'number' => 1,
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
        ]);

        $emptyFeature = IssueFactory::createOne([
            'number' => 2,
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => IssueTypeFactory::featureType(),
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true
        ]);

        SprintGoalFactory::createOne([
            'sprint' => $sprint,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/sprints/current/issues',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'issueIds' => [
                $issue->getId(),
                $emptyFeature->getId()
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $sprintGoalIssues = $this->sprintGoalIssueRepository()->findAll();

        $this->assertCount(2, $sprintGoalIssues);

        $this->assertTrue($issue->getIssueColumn()->isToDo());
        $this->assertTrue($emptyFeature->getIssueColumn()->isToDo());
    }

    private function sprintGoalIssueRepository(): SprintGoalIssueRepository
    {
        return $this->getService(SprintGoalIssueRepository::class);
    }

    private function sprintGoalRepository(): SprintGoalRepository
    {
        return $this->getService(SprintGoalRepository::class);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
