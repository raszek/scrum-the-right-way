<?php

namespace App\Tests\Controller\Room;

use App\Factory\Issue\IssueFactory;
use App\Factory\Room\RoomFactory;
use App\Factory\Room\RoomIssueFactory;
use App\Helper\JsonHelper;
use App\Service\Jwt\Websocket\WebsocketJwtService;
use App\Service\Jwt\Websocket\WebsocketJwtServiceFactory;
use App\Tests\Controller\WebTestCase;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\UserFactory;
use App\Service\Jwt\Websocket\WebsocketJwtPayload;

class RoomControllerApiTest extends WebTestCase
{
    /** @test */
    public function user_cannot_access_project_when_it_is_not_authenticated()
    {
        $client = static::createClient();
        $client->followRedirects();

        $project = ProjectFactory::createOne();

        $room = RoomFactory::createOne();

        $client->request('GET', sprintf('/projects/%s/rooms/%s/access', $project->getId(), $room->getId()));

        $this->assertResponseStatusCodeSame(401);
    }

    /** @test */
    public function user_cannot_access_project_when_he_is_not_project_member()
    {
        $client = static::createClient();
        $client->followRedirects();

        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne();

        $room = RoomFactory::createOne();

        $websocketJwtService = $this->websocketJwtService();

        $jwtToken = $websocketJwtService->encode(new WebsocketJwtPayload(
            id: $user->getId(),
            fullName: $user->getFullName(),
        ));

        $client->request('GET', sprintf('/projects/%s/rooms/%s/access', $project->getId(), $room->getId()), server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwtToken),
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    /** @test */
    public function user_can_access_project()
    {
        $client = static::createClient();
        $client->followRedirects();

        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project,
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'number' => 1,
            'storyPoints' => 13
        ]);

        $room = RoomFactory::createOne([
            'project' => $project,
        ]);

        RoomIssueFactory::createOne([
            'room' => $room,
            'issue' => $issue,
        ]);

        $websocketJwtService = $this->websocketJwtService();

        $jwtToken = $websocketJwtService->encode(new WebsocketJwtPayload(
            id: $user->getId(),
            fullName: $user->getFullName(),
        ));

        $client->request('GET', sprintf('/projects/%s/rooms/%s/access', $project->getId(), $room->getId()), server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $jwtToken),
        ]);

        $this->assertResponseIsSuccessful();

        $response = JsonHelper::decode($client->getResponse()->getContent());

        $this->assertArrayHasKey('id', $response);
        $this->assertEquals(13, $response['storyPoints']);;
    }

    private function websocketJwtService(): WebsocketJwtService
    {
        return $this->getService(WebsocketJwtServiceFactory::class)->create();
    }
}
