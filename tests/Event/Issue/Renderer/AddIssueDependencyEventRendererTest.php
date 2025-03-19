<?php

namespace App\Tests\Event\Issue\Renderer;

use App\Event\Issue\Event\AddIssueDependencyEvent;
use App\Event\Issue\Renderer\AddIssueDependencyEventRenderer;
use App\Event\Issue\Renderer\IssueDependencyEventRenderer;
use App\Event\RendererUrlGenerator;
use App\Factory\Event\EventFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\IssueRepository;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class AddIssueDependencyEventRendererTest extends KernelTestCase
{



    /** @test */
    public function it_renders_add_issue_dependency_event()
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

        $dependency = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task5',
            'number' => 15
        ]);

        $eventData = new AddIssueDependencyEvent(
            issueId: $issue->getId(),
            dependencyId: $dependency->getId()
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
            '<b>Arlene Legros</b> has added to <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> dependency <a href="http://localhost/projects/%s/issues/SCP-15">task5</a>',
            $project->getId(),
            $project->getId(),
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    /** @test */
    public function it_renders_add_issue_dependency_event_for_issue()
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

        $dependency = IssueFactory::createOne([
            'project' => $project,
            'title' => 'task5',
            'number' => 15
        ]);

        $eventData = new AddIssueDependencyEvent(
            issueId: $issue->getId(),
            dependencyId: $dependency->getId()
        );

        $event = EventFactory::new()
            ->withEvent($eventData)
            ->create([
                'project' => $project,
                'createdBy' => $user,
            ]);

        $event->setData($eventData);

        $renderer = $this->create();

        $eventRecord = $renderer->fetchForIssue([$event->_real()])[0];

        $expectations = sprintf(
            '<b>Arlene Legros</b> has added dependency <a href="http://localhost/projects/%s/issues/SCP-15">task5</a>',
            $project->getId(),
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    private function create(): AddIssueDependencyEventRenderer
    {
        $renderer = new IssueDependencyEventRenderer(
            issueRepository: $this->getService(IssueRepository::class),
        );

        return new AddIssueDependencyEventRenderer(
            renderer: $renderer,
            urlGenerator: $this->getService(RendererUrlGenerator::class),
        );
    }
}
