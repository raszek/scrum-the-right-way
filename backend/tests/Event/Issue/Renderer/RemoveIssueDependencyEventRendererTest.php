<?php

namespace App\Tests\Event\Issue\Renderer;

use App\Event\Issue\Event\RemoveIssueDependencyEvent;
use App\Event\Issue\Renderer\IssueDependencyEventRenderer;
use App\Event\Issue\Renderer\RemoveIssueDependencyEventRenderer;
use App\Event\RendererUrlGenerator;
use App\Factory\Event\EventFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\IssueRepository;
use App\Tests\KernelTestCase;

class RemoveIssueDependencyEventRendererTest extends KernelTestCase
{

    /** @test */
    public function it_renders_remove_issue_dependency_event()
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

        $eventData = new RemoveIssueDependencyEvent(
            issueId: $issue->getId()->integerId(),
            dependencyId: $dependency->getId()->integerId(),
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
            '<b>Arlene Legros</b> has removed from <a href="http://localhost/projects/%s/issues/SCP-11">issue</a> dependency <a href="http://localhost/projects/%s/issues/SCP-15">task5</a>',
            $project->getId(),
            $project->getId(),
        );

        $this->assertEquals($expectations, $eventRecord->content);
    }

    private function create(): RemoveIssueDependencyEventRenderer
    {
        $renderer = new IssueDependencyEventRenderer(
            issueRepository: $this->getService(IssueRepository::class),
        );

        return new RemoveIssueDependencyEventRenderer(
            renderer: $renderer,
            urlGenerator: $this->getService(RendererUrlGenerator::class),
        );
    }
}
