<?php

namespace App\Tests\Command;


use App\Event\Issue\IssueEventList;
use App\Factory\Event\EventFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserNotificationFactory;
use App\Factory\UserFactory;
use App\Tests\KernelTestCase;
use Carbon\CarbonImmutable;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class NotificationSendEmailsCommandTest extends KernelTestCase
{

    /** @test */
    public function cron_can_send_notification_emails()
    {
        self::bootKernel();

        $user = UserFactory::createOne([
            'firstName' => 'Leny',
            'lastName' => 'Benny'
        ]);

        $analytic = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros'
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

        $notificationOne = UserNotificationFactory::createOne([
            'forUser' => $user,
            'event' => $event,
            'read' => false,
            'sentEmail' => false
        ]);

        $notificationTwo = UserNotificationFactory::createOne([
            'forUser' => $user,
            'event' => $event,
            'read' => false,
            'sentEmail' => true
        ]);

        $notificationThree = UserNotificationFactory::createOne([
            'forUser' => $user,
            'event' => $event,
            'read' => true,
            'sentEmail' => false
        ]);

        $notificationFour = UserNotificationFactory::createOne([
            'forUser' => $analytic,
            'event' => $event,
            'read' => false,
            'sentEmail' => false
        ]);

        $application = new Application(self::$kernel);

        $command = $application->find('notification:send-emails');
        $commandTester = new CommandTester($command);
        $status = $commandTester->execute([]);

        $this->assertEquals(0 , $status);

        $notificationOne->_refresh();
        $this->assertTrue($notificationOne->isSentEmail());
        $this->assertFalse($notificationOne->isRead());

        $notificationTwo->_refresh();
        $this->assertTrue($notificationTwo->isSentEmail());
        $this->assertFalse($notificationTwo->isRead());

        $notificationThree->_refresh();
        $this->assertFalse($notificationThree->isSentEmail());
        $this->assertTrue($notificationThree->isRead());

        $notificationFour->_refresh();
        $this->assertTrue($notificationFour->isSentEmail());
        $this->assertFalse($notificationFour->isRead());

        $messages = $this->getMailerMessages();

        $this->assertCount(2, $messages);
    }

}
