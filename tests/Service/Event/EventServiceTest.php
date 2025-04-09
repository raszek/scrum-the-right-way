<?php

namespace App\Tests\Service\Event;

use App\Event\Issue\Event\AddIssueThreadMessageEvent;
use App\Event\Issue\Event\CreateIssueEvent;
use App\Event\Issue\Event\RemoveIssueThreadMessageEvent;
use App\Event\Issue\Event\SetIssueAssigneeEvent;
use App\Event\Issue\Event\SetIssueDescriptionEvent;
use App\Event\Issue\Event\SetIssueStoryPointsEvent;
use App\Event\Issue\Event\SetIssueTagsEvent;
use App\Event\Project\Event\RemoveMemberEvent;
use App\Event\Thread\Event\AddThreadMessageEvent;
use App\Factory\Issue\DescriptionHistoryFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Thread\ThreadFactory;
use App\Factory\Thread\ThreadMessageFactory;
use App\Service\Event\EventPersisterFactory;
use App\Service\Event\EventService;
use App\Tests\KernelTestCase;
use App\Enum\Project\ProjectRoleEnum;
use App\Event\Project\Event\AddRoleEvent;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;

class EventServiceTest extends KernelTestCase
{



    /** @test */
    public function remove_member_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $userWithAddedRole = UserFactory::createOne([
            'firstName' => 'Mary',
            'lastName' => 'Nader'
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new RemoveMemberEvent(
            $userWithAddedRole->getId(),
        );

        $event = $eventPersister->create($eventData);

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $this->assertEquals('<b>Arlene Legros</b> has removed user <b>Mary Nader</b> from project', $eventRecord->content);
    }

    /** @test */
    public function add_thread_message_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne();

        $createdThread = ThreadFactory::createOne([
            'project' => $project,
            'title' => 'Test title',
            'slug' => 'Test-title'
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Joanny',
            'lastName' => 'Watson',
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new AddThreadMessageEvent(
            threadId: $createdThread->getId()->integerId()
        );

        $event = $eventPersister->create($eventData);

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $this->assertEquals('Joanny Watson has added new message to thread Test title', strip_tags($eventRecord->content));
    }

    /** @test */
    public function add_role_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $userWithAddedRole = UserFactory::createOne([
            'firstName' => 'Mary',
            'lastName' => 'Nader'
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new AddRoleEvent(
            $userWithAddedRole->getId(),
            ProjectRoleEnum::Developer->value
        );

        $event = $eventPersister->create($eventData);

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $this->assertEquals('<b>Arlene Legros</b> has added role <b>Developer</b> to user <b>Mary Nader</b>', $eventRecord->content);
    }

    /** @test */
    public function create_issue_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new CreateIssueEvent(
            $issue->getId()->integerId(),
        );

        $event = $eventPersister->create($eventData);

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has created issue <a href="/projects/%s/backlog/issues/SCP-11">task1</a>',
            $project->getId()
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function set_assignee_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP',
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $assigneeUser = UserFactory::createOne([
            'firstName' => 'Brock',
            'lastName' => 'Conn'
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new SetIssueAssigneeEvent(
            issueId: $issue->getId()->integerId(),
            userId: $assigneeUser->getId()
        );

        $event = $eventPersister->createIssueEvent($eventData, $issue->_real());

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has assigned issue <a href="http://localhost/projects/%s/issues/SCP-11">task1</a> to user <b>Brock Conn</b>',
            $project->getId()
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function set_issue_description_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP',
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $descriptionHistory = DescriptionHistoryFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new SetIssueDescriptionEvent(
            issueId: $issue->getId()->integerId(),
            historyId: $descriptionHistory->getId()->integerId()
        );

        $event = $eventPersister->createIssueEvent($eventData, $issue->_real());

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has changed <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> description (<a href="http://localhost/projects/%s/issues/SCP-11/description-history/%s/show" target="_blank">diff</a>)',
            $project->getId(),
            $project->getId(),
            $descriptionHistory->getId()
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function set_issue_story_points_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP',
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new SetIssueStoryPointsEvent(
            issueId: $issue->getId()->integerId(),
            storyPoints: 13
        );

        $event = $eventPersister->createIssueEvent($eventData, $issue->_real());

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has changed <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> story points to 13',
            $project->getId(),
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function set_issue_tags_event_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP',
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new SetIssueTagsEvent(
            issueId: $issue->getId()->integerId(),
            tags: ['CODE_REVIEW', 'DEV']
        );

        $event = $eventPersister->createIssueEvent($eventData, $issue->_real());

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has changed <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> tags to CODE_REVIEW, DEV',
            $project->getId(),
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function add_issue_thread_message_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP',
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $thread = ThreadFactory::createOne([
            'project' => $project,
            'title' => 'Some title',
            'slug' => 'some-title'
        ]);

        $threadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'number' => 1
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new AddIssueThreadMessageEvent(
            issueId: $issue->getId()->integerId(),
            threadMessageId: $threadMessage->getId()->integerId()
        );

        $event = $eventPersister->createIssueEvent($eventData, $issue->_real());

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has added to <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> thread message <a href="http://localhost/projects/%s/threads/%s/some-title/messages#1">Some title #1</a>',
            $project->getId(),
            $project->getId(),
            $thread->getId()
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function remove_issue_thread_message_renderer_can_generate_content()
    {
        $project = ProjectFactory::createOne([
            'code' => 'SCP',
        ]);

        $user = UserFactory::createOne([
            'firstName' => 'Arlene',
            'lastName' => 'Legros',
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task1',
            'number' => 11
        ]);

        $thread = ThreadFactory::createOne([
            'project' => $project,
            'title' => 'Some title',
            'slug' => 'some-title'
        ]);

        $threadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'number' => 1
        ]);

        $eventPersister = $this->factory()->create($project->_real(), $user->_real());

        $eventData = new RemoveIssueThreadMessageEvent(
            issueId: $issue->getId()->integerId(),
            threadMessageId: $threadMessage->getId()->integerId()
        );

        $event = $eventPersister->createIssueEvent($eventData, $issue->_real());

        $eventService = $this->create();

        $eventRecord = $eventService->getEventRecords([$event])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has removed from <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> thread message <a href="http://localhost/projects/%s/threads/%s/some-title/messages#1">Some title #1</a>',
            $project->getId(),
            $project->getId(),
            $thread->getId()
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    private function create(): EventService
    {
        return $this->getService(EventService::class);
    }

    private function factory(): EventPersisterFactory
    {
        return $this->getService(EventPersisterFactory::class);
    }
}
