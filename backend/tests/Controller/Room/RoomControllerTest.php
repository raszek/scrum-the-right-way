<?php

namespace App\Tests\Controller\Room;


use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Room\RoomFactory;
use App\Factory\Room\RoomIssueFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\UserFactory;
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

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project,
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

}
