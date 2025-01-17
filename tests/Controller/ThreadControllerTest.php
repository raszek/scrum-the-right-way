<?php

namespace App\Tests\Controller;

use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Thread\ThreadFactory;
use App\Factory\Thread\ThreadMessageFactory;
use App\Factory\Thread\ThreadStatusFactory;
use App\Factory\UserFactory;
use App\Repository\Thread\ThreadMessageRepository;
use App\Repository\Thread\ThreadRepository;
use App\Repository\Thread\ThreadStatusRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use Zenstruck\Foundry\Test\Factories;

class ThreadControllerTest extends WebTestCase
{

    use Factories;

    /** @test */
    public function project_member_can_list_threads()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $threadOpenStatus = ThreadStatusFactory::openStatus();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $threadOne = ThreadFactory::createOne([
            'title' => 'example project title',
            'project' => $project,
            'status' => $threadOpenStatus
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $threadOne,
            'createdBy' => $user
        ]);

        $threadTwo = ThreadFactory::createOne([
            'title' => 'other title',
            'project' => $project,
            'status' => $threadOpenStatus
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $threadTwo,
            'createdBy' => $user
        ]);

        $this->loginAsUser($user);

        $this->goToPage(
            sprintf(
                '/projects/%s/threads',
                $project->getId()
            )
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('example project title');
        $this->assertResponseHasText('other title');
    }

    /** @test */
    public function project_member_can_search_by_thread_title()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $threadOpenStatus = ThreadStatusFactory::openStatus();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $threadOne = ThreadFactory::createOne([
            'title' => 'example project title',
            'project' => $project,
            'status' => $threadOpenStatus
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $threadOne,
            'createdBy' => $user
        ]);

        $threadTwo = ThreadFactory::createOne([
            'title' => 'other title',
            'project' => $project,
            'status' => $threadOpenStatus
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $threadTwo,
            'createdBy' => $user
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe(
            sprintf(
                '/projects/%s/threads',
                $project->getId()
            )
        );

        $form = $crawler->selectButton('Search')->form();

        $client->submit($form, [
            'search_thread[title]' => 'other',
        ]);
        
        $this->assertResponseIsSuccessful();

        $table = $this->readTable('table');

        $this->assertEquals('other title', $table[1][0]);
    }


    /** @test */
    public function project_member_can_create_thread()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        ThreadStatusFactory::threadStatuses();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe(
            sprintf(
                '/projects/%s/threads/create',
                $project->getId()
            )
        );

        $form = $crawler->selectButton('Create')->form();

        $client->submit($form, [
            'thread[title]' => 'some thread title',
            'thread[message]' => 'some thread message',
        ]);

        $this->assertResponseIsSuccessful();

        $createdThread = $this->threadRepository()->findOneBy([
            'title' => 'some thread title'
        ]);

        $this->assertNotNull($createdThread);
        $this->assertEquals($createdThread->getCreatedBy()->getId(), $user->getId());

        $threadMessages = $createdThread->getThreadMessages();

        $this->assertCount(1, $threadMessages);

        $firstMessage = $threadMessages->get(0);

        $this->assertNotNull($firstMessage);
        $this->assertEquals('some thread message', $firstMessage->getContent());
        $this->assertEquals(1, $firstMessage->getNumber());
    }

    /** @test */
    public function project_member_can_access_thread_messages()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $thread = ThreadFactory::createOne([
            'title' => 'example project title',
            'project' => $project
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $thread,
            'createdBy' => $user,
            'content' => 'First message',
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $thread,
            'createdBy' => $user,
            'content' => 'Second message',
        ]);

        $this->loginAsUser($user);

        $this->goToPage(
            sprintf(
                '/projects/%s/threads/%s/%s/messages',
                $project->getId(),
                $thread->getId(),
                $thread->getSlug()
            )
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('First message');
        $this->assertResponseHasText('Second message');
    }

    /** @test */
    public function project_member_can_add_thread_message()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2012, 12, 12, 12, 12, 12);
            }
        };

        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $thread = ThreadFactory::createOne([
            'title' => 'example project title',
            'project' => $project
        ]);

        ThreadMessageFactory::createOne([
            'number' => 1,
            'thread' => $thread,
            'createdBy' => $user,
            'content' => 'First message',
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe(
            sprintf(
                '/projects/%s/threads/%s/%s/messages',
                $project->getId(),
                $thread->getId(),
                $thread->getSlug()
            )
        );

        $form = $crawler->selectButton('Add comment')->form();

        $client->submit($form, [
            'message[content]' => 'Some comment',
        ]);

        $this->assertResponseIsSuccessful();

        $updatedThread = $this->threadRepository()->findOneBy([
            'id' => $thread->getId()
        ]);

        $this->assertNotNull($updatedThread);
        $this->assertEquals('2012-12-12 12:12:12', $updatedThread->getUpdatedAt()->format('Y-m-d H:i:s'));

        $this->assertCount(2, $updatedThread->getThreadMessages());
        $addedMessage = $updatedThread->getThreadMessages()->get(1);

        $this->assertEquals(2, $addedMessage->getNumber());
        $this->assertEquals('Some comment', $addedMessage->getContent());
    }

    /** @test */
    public function project_member_can_close_thread()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2012, 12, 12, 12, 12, 12);
            }
        };

        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        ThreadStatusFactory::threadStatuses();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $thread = ThreadFactory::createOne([
            'title' => 'project title',
            'project' => $project,
            'status' => $this->threadStatusRepository()->openStatus()
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $thread,
            'createdBy' => $user,
            'content' => 'First message',
        ]);

        $this->loginAsUser($user);

        $this->goToPage(
            sprintf(
                '/projects/%s/threads/%s/close',
                $project->getId(),
                $thread->getId()
            )
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Closed thread "project title". You can open it again if needed.');

        $updatedThread = $this->threadRepository()->findOneBy([
            'id' => $thread->getId()
        ]);

        $this->assertNotNull($updatedThread);
        $this->assertEquals(
            $updatedThread->getStatus()->getId(),
            $this->threadStatusRepository()->closedStatus()->getId()
        );
        $this->assertEquals('2012-12-12 12:12:12', $updatedThread->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function project_member_can_reopen_thread()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2012, 12, 12, 12, 12, 12);
            }
        };

        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        ThreadStatusFactory::threadStatuses();

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $thread = ThreadFactory::createOne([
            'title' => 'project title',
            'project' => $project,
            'status' => $this->threadStatusRepository()->closedStatus()
        ]);

        ThreadMessageFactory::createOne([
            'thread' => $thread,
            'createdBy' => $user,
            'content' => 'First message',
        ]);

        $this->loginAsUser($user);

        $this->goToPage(
            sprintf(
                '/projects/%s/threads/%s/open',
                $project->getId(),
                $thread->getId()
            )
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Reopened thread "project title".');

        $updatedThread = $this->threadRepository()->findOneBy([
            'id' => $thread->getId()
        ]);

        $this->assertNotNull($updatedThread);
        $this->assertEquals(
            $updatedThread->getStatus()->getId(),
            $this->threadStatusRepository()->openStatus()->getId()
        );
        $this->assertEquals('2012-12-12 12:12:12', $updatedThread->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    private function threadStatusRepository(): ThreadStatusRepository
    {
        return $this->getService(ThreadStatusRepository::class);
    }

    private function threadMessageRepository(): ThreadMessageRepository
    {
        return $this->getService(ThreadMessageRepository::class);
    }

    private function threadRepository(): ThreadRepository
    {
        return $this->getService(ThreadRepository::class);
    }
}
