<?php

namespace App\Tests\Controller;

use App\Event\Issue\IssueEventList;
use App\Factory\Event\EventFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserNotificationFactory;
use App\Factory\UserFactory;
use App\Repository\User\UserNotificationRepository;
use Carbon\CarbonImmutable;

class UserNotificationControllerTest extends WebTestCase
{

    /** @test */
    public function user_can_list_his_notifications()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros'
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Brock',
            'lastName' => 'Conn'
        ]);

        $project = ProjectFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $event = EventFactory::createOne([
            'name' => IssueEventList::SET_ISSUE_ASSIGNEE,
            'params' => [
                'userId' => $user->getId()->integerId(),
                'issueId' => $issue->getId()->integerId()
            ],
            'project' => $project,
            'createdBy' => $analytic,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12),
        ]);

        UserNotificationFactory::createOne([
            'forUser' => $user,
            'event' => $event
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/user/notifications');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Arlene Legros has assigned issue task1 to user Brock Conn');
    }

    /** @test */
    public function user_can_list_his_latest_notifications()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros'
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Brock',
            'lastName' => 'Conn'
        ]);

        $project = ProjectFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $event = EventFactory::createOne([
            'name' => IssueEventList::SET_ISSUE_ASSIGNEE,
            'params' => [
                'userId' => $user->getId()->integerId(),
                'issueId' => $issue->getId()->integerId()
            ],
            'project' => $project,
            'createdBy' => $analytic,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12),
        ]);

        UserNotificationFactory::createOne([
            'forUser' => $user,
            'event' => $event
        ]);

        $this->loginAsUser($user);

        $this->goToPage('/user/notifications/latest');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Arlene Legros has assigned issue task1 to user Brock Conn');
    }

    /** @test */
    public function user_can_mark_his_notification_as_read()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $userNotification = UserNotificationFactory::createOne([
            'forUser' => $user,
            'read' => false
        ]);

        $this->loginAsUser($user);

        $client->request('POST', sprintf('/user/notifications/%s/mark-read', $userNotification->getId()));

        $this->assertResponseIsSuccessful();

        $updatedNotification = $this->userNotificationRepository()->findOneBy([
            'id' => $userNotification->getId()
        ]);

        $this->assertNotNull($updatedNotification);
        $this->assertTrue($updatedNotification->isRead());
    }

    /** @test */
    public function user_can_mark_his_notification_as_unread()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $userNotification = UserNotificationFactory::createOne([
            'forUser' => $user,
            'read' => true
        ]);

        $this->loginAsUser($user);

        $client->request('POST', sprintf('/user/notifications/%s/mark-unread', $userNotification->getId()));

        $this->assertResponseIsSuccessful();

        $updatedNotification = $this->userNotificationRepository()->findOneBy([
            'id' => $userNotification->getId()
        ]);

        $this->assertNotNull($updatedNotification);
        $this->assertFalse($updatedNotification->isRead());
    }

    /** @test */
    public function user_can_mark_all_his_notification_as_read()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        UserNotificationFactory::createMany(4, [
            'forUser' => $user,
            'read' => false
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/user/notifications/mark-all-read');

        $this->assertResponseIsSuccessful();

        $updatedNotifications = $this->userNotificationRepository()->findBy([
            'forUser' => $user->getId(),
            'isRead' => true
        ]);

        $this->assertCount(4, $updatedNotifications);
    }

    private function userNotificationRepository(): UserNotificationRepository
    {
        return $this->getService(UserNotificationRepository::class);
    }
}
