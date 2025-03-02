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
use App\Factory\UserFactory;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Tests\Controller\WebTestCase;

class SprintControllerTest extends WebTestCase
{

    /** @test */
    public function developer_can_add_issue_to_sprint_in_first_sprint_goal_only()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne([
            'firstName' => 'Samba',
            'lastName' => 'Bamba',
        ]);

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

    private function sprintGoalRepository(): SprintGoalRepository
    {
        return $this->getService(SprintGoalRepository::class);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
