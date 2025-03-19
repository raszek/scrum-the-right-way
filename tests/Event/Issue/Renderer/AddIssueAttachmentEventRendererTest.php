<?php

namespace App\Tests\Event\Issue\Renderer;

use App\Event\Issue\Event\AddIssueAttachmentEvent;
use App\Event\Issue\Renderer\AddIssueAttachmentEventRenderer;
use App\Event\RendererUrlGenerator;
use App\Factory\Event\EventFactory;
use App\Factory\FileFactory;
use App\Factory\Issue\AttachmentFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\AttachmentRepository;
use App\Repository\Issue\IssueRepository;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class AddIssueAttachmentEventRendererTest extends KernelTestCase
{



    /** @test */
    public function it_renders_add_attachment_activity()
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

        $file = FileFactory::createOne([
            'name' => 'test.png',
        ]);

        $attachment = AttachmentFactory::createOne([
            'issue' => $issue,
            'file' => $file,
        ]);

        $eventData = new AddIssueAttachmentEvent(
            issueId: $issue->getId(),
            attachmentId: $attachment->getId()->integerId()
        );

        $event = EventFactory::new()
            ->withEvent($eventData)
            ->create([
                'project' => $project,
                'createdBy' => $user,
            ]);

        $event->setData($eventData);

        $renderer = $this->create();

        $eventRecord = $renderer->fetch([$event->_real()])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has added to <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> attachment <a href="http://localhost/projects/%s/issues/SCP-11/attachments/%s">test.png</a>',
            $project->getId(),
            $project->getId(),
            $attachment->getId(),
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    private function create(): AddIssueAttachmentEventRenderer
    {
        return new AddIssueAttachmentEventRenderer(
            issueRepository: $this->getService(IssueRepository::class),
            attachmentRepository: $this->getService(AttachmentRepository::class),
            urlGenerator: $this->getService(RendererUrlGenerator::class),
        );
    }

}
