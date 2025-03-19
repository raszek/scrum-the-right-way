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
use App\Tests\Controller\WebTestCase;

class SprintControllerTest extends WebTestCase
{



    /** @test */
    public function user_can_view_current_sprint()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
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

        IssueFactory::createOne([
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

    private function sprintGoalRepository(): SprintGoalRepository
    {
        return $this->getService(SprintGoalRepository::class);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
