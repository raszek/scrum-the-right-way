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
use App\Factory\Project\ProjectTagFactory;
use App\Factory\UserFactory;
use App\Repository\Event\EventRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\User\UserNotificationRepository;
use Zenstruck\Foundry\Test\Factories;

class SingleIssueControllerTest extends WebTestCase
{
    use Factories;

    /** @test */
    public function project_developer_can_edit_issue_title()
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

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
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
    public function project_developer_can_edit_issue_description()
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

        $developerRole = ProjectRoleFactory::developerRole();

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
    public function developer_can_assign_story_points_to_issue()
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

        $developerRole = ProjectRoleFactory::developerRole();

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
    public function developer_can_set_issue_assignee_to_another_developer()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $anotherDeveloper = UserFactory::createOne([
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

        $memberAnotherDeveloper = ProjectMemberFactory::createOne([
            'user' => $anotherDeveloper,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnotherDeveloper,
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

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/assignees',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'projectMemberId' => $memberAnotherDeveloper->getId()
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertNotNull($updatedIssue->getAssignee());
        $this->assertEquals($updatedIssue->getAssignee()->getId(), $memberAnotherDeveloper->getId());

        $this->assertEquals(1, $updatedIssue->getObservers()->count());

        $issueObserver = $updatedIssue->getObservers()->get(0);
        $this->assertEquals('Samba Bamba', $issueObserver->getFullName());

        $events = $this->eventRepository()->findAll();
        $this->assertCount(1, $events);

        $developerNotifications = $this->userNotificationRepository()->findBy([
            'forUser' => $anotherDeveloper->getId(),
        ]);

        $this->assertCount(1, $developerNotifications);

        $developerNotifications = $this->userNotificationRepository()->findBy([
            'forUser' => $developer->getId(),
        ]);
        $this->assertCount(0, $developerNotifications);
    }

    /** @test */
    public function developer_can_set_issue_assignee_to_none()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $anotherDeveloper = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $memberAnotherDeveloper = ProjectMemberFactory::createOne([
            'user' => $anotherDeveloper,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnotherDeveloper,
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

        $this->loginAsUser($developer);

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
    public function developer_can_set_issue_tags()
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

        $this->loginAsUser($developer);

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

    /** @test */
    public function developer_can_archive_issue()
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

        IssueColumnFactory::archivedColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/archive',
            $project->getId(),
        );

        $client->request('POST', $uri);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertNotNull($updatedIssue);
        $this->assertEquals(IssueColumnEnum::Archived->value, $updatedIssue->getIssueColumn()->getId());
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
}
