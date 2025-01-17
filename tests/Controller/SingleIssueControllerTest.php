<?php

namespace App\Tests\Controller;

use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\Project\ProjectTagFactory;
use App\Factory\UserFactory;
use App\Repository\Event\EventRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Project\ProjectTagRepository;
use App\Repository\User\UserNotificationRepository;
use Zenstruck\Foundry\Test\Factories;

class SingleIssueControllerTest extends WebTestCase
{
    use Factories;

    /** @test */
    public function project_analytic_can_edit_issue_title()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'title' => 'Title to be edited',
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/update-title', [
            'title' => 'New title for issue'
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertEquals('New title for issue', $updatedIssue->getTitle());
    }

    /** @test */
    public function project_analytic_can_edit_issue_description()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
            'description' => null
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/update-description', [
            'description' => 'New description for issue'
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertEquals('New description for issue', $updatedIssue->getDescription());

        $descriptionChange = $updatedIssue->getDescriptionChanges()->get(0);

        $this->assertNotNull($descriptionChange);
        $this->assertNotNull($descriptionChange->getChanges());
    }

    /** @test */
    public function analytic_can_assign_story_points_to_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
            'storyPoints' => 3
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/set-story-points', [
            'points' => 13
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertEquals(13, $updatedIssue->getStoryPoints());
    }

    /** @test */
    public function analytic_can_set_issue_assignee_to_developer()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne([
            'firstName' => 'Samba',
            'lastName' => 'Bamba',
        ]);

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/assignees',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'projectMemberId' => $memberDeveloper->getId()
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertNotNull($updatedIssue->getAssignee());
        $this->assertEquals($updatedIssue->getAssignee()->getId(), $memberDeveloper->getId());

        $this->assertEquals(1, $updatedIssue->getObservers()->count());

        $issueObserver = $updatedIssue->getObservers()->get(0);
        $this->assertEquals('Samba Bamba', $issueObserver->getFullName());

        $events = $this->eventRepository()->findAll();
        $this->assertCount(1, $events);

        $developerNotifications = $this->userNotificationRepository()->findBy([
            'forUser' => $developer->getId(),
        ]);

        $this->assertCount(1, $developerNotifications);

        $analyticNotifications = $this->userNotificationRepository()->findBy([
            'forUser' => $analytic->getId(),
        ]);
        $this->assertCount(0, $analyticNotifications);
    }

    /** @test */
    public function analytic_can_set_issue_assignee_to_none()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
            'assignee' => $memberDeveloper
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/assignees',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'projectMemberId' => null
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertNull($updatedIssue->getAssignee());
    }


    /** @test */
    public function analytic_can_set_issue_tags()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        ProjectTagFactory::createOne([
            'project' => $project,
            'name' => 'CODE_REVIEW',
        ]);

        ProjectTagFactory::createOne([
            'project' => $project,
            'name' => 'sprint',
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/tags',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'tags' => 'CODE_REVIEW,sprint',
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertNotNull($updatedIssue);
        $this->assertCount(2, $updatedIssue->getTags());
        $this->assertEquals('CODE_REVIEW', $updatedIssue->getTags()->get(0)->getName());
        $this->assertEquals('sprint', $updatedIssue->getTags()->get(1)->getName());
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

    private function eventRepository(): EventRepository
    {
        return $this->getService(EventRepository::class);
    }

    private function userNotificationRepository(): UserNotificationRepository
    {
        return $this->getService(UserNotificationRepository::class);
    }

    private function projectTagRepository(): ProjectTagRepository
    {
        return $this->getService(ProjectTagRepository::class);
    }
}
