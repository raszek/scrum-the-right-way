<?php

namespace App\Tests\Controller\Room;

use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\Room\RoomFactory;
use App\Factory\Room\RoomIssueFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\UserFactory;
use App\Helper\JsonHelper;
use App\Tests\Controller\WebTestCase;

class RoomIssueControllerTest extends WebTestCase
{

    /** @test */
    public function room_user_can_display_issues()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        $developerRole = ProjectRoleFactory::developerRole();

        $projectMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project,
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $developerRole,
            'projectMember' => $projectMember,
        ]);

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'title' => 'Some issue title',
            'description' => 'Some issue description',
        ]);

        $room = RoomFactory::createOne([
            'project' => $project,
        ]);

        RoomIssueFactory::createOne([
            'issue' => $issue,
            'room' => $room,
        ]);

        $this->loginAsUser($user);

        $this->goToPage(
            sprintf(
                '/projects/%s/rooms/%s/issues/%s',
                $project->getId(),
                $room->getId(),
                $issue->getId()
            )
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('SCP-1');
        $this->assertResponseHasText('Some issue title');
        $this->assertResponseHasText('Some issue description');
    }

    /** @test */
    public function room_user_can_search_for_new_issues()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        $developerRole = ProjectRoleFactory::developerRole();

        $projectMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project,
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $developerRole,
            'projectMember' => $projectMember,
        ]);

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'title' => 'Some issue title',
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'number' => 2,
            'title' => 'Another issue title',
        ]);

        $room = RoomFactory::createOne([
            'project' => $project,
        ]);

        RoomIssueFactory::createOne([
            'issue' => $issue,
            'room' => $room,
        ]);

        $this->loginAsUser($user);

        $url = sprintf(
            '/projects/%s/rooms/%s/issues?query=issue',
            $project->getId(),
            $room->getId(),
        );

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        $records = JsonHelper::decode($client->getResponse()->getContent());

        $this->assertCount(1, $records);
    }

    /** @test */
    public function developer_can_add_issue_to_room()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        $developerRole = ProjectRoleFactory::developerRole();

        $projectMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project,
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $developerRole,
            'projectMember' => $projectMember,
        ]);

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'title' => 'Some issue title',
        ]);

        $newIssue = IssueFactory::createOne([
            'project' => $project,
            'number' => 2,
            'title' => 'Another issue title',
        ]);

        $room = RoomFactory::createOne([
            'project' => $project,
        ]);

        RoomIssueFactory::createOne([
            'issue' => $issue,
            'room' => $room,
        ]);

        $this->loginAsUser($user);

        $url = sprintf(
            '/projects/%s/rooms/%s/issues',
            $project->getId(),
            $room->getId(),
        );

        $client->request('POST', $url, [
            'issueId' => $newIssue->getId()->get(),
        ]);

        $this->assertResponseStatusCodeSame(204);

        $this->assertCount(2, $room->getRoomIssues());
    }

    /** @test */
    public function developer_can_remove_issue_from_room()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create([
                'code' => 'SCP'
            ]);

        $developerRole = ProjectRoleFactory::developerRole();

        $projectMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project,
        ]);

        ProjectMemberRoleFactory::createOne([
            'role' => $developerRole,
            'projectMember' => $projectMember,
        ]);

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'title' => 'Some issue title',
        ]);

        $secondIssue = IssueFactory::createOne([
            'project' => $project,
            'number' => 2,
            'title' => 'Another issue title',
        ]);

        $room = RoomFactory::createOne([
            'project' => $project,
        ]);

        RoomIssueFactory::createOne([
            'issue' => $issue,
            'room' => $room,
        ]);

        RoomIssueFactory::createOne([
            'issue' => $secondIssue,
            'room' => $room,
        ]);

        $this->loginAsUser($user);

        $url = sprintf(
            '/projects/%s/rooms/%s/issues/%s/remove',
            $project->getId(),
            $room->getId(),
            $secondIssue->getId()->get()
        );

        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame(204);

        $this->assertCount(1, $room->getRoomIssues());
    }
}
