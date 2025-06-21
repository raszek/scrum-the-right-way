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
use App\Repository\Room\RoomRepository;
use App\Tests\Controller\WebTestCase;

class RoomControllerTest extends WebTestCase
{

    /** @test */
    public function project_member_can_access_scrum_poker_room()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create();

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
            'number' => 1
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
                '/projects/%s/rooms/%s',
                $project->getId(),
                $room->getId()
            )
        );

        $this->assertResponseIsSuccessful();
    }

    /** @test */
    public function project_member_can_create_scrum_poker_room()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create();

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

        $issueOne = IssueFactory::createOne([
            'project' => $project,
            'number' => 1
        ]);

        $issueTwo = IssueFactory::createOne([
            'project' => $project,
            'number' => 2
        ]);

        $this->loginAsUser($user);

        $client->request('POST', sprintf('/projects/%s/rooms', $project->getId()), [
            'issueIds' => [
                $issueOne->getId()->get(),
                $issueTwo->getId()->get(),
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $rooms = $this->roomRepository()->findAll();

        $this->assertCount(1, $rooms);

        $createdRoom = $rooms[0];

        $this->assertEquals($project->getId(), $createdRoom->getProject()->getId());
        $this->assertCount(2, $createdRoom->getRoomIssues());
    }

    private function roomRepository(): RoomRepository
    {
        return $this->getService(RoomRepository::class);
    }
}
