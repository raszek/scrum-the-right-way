<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Event\EventRecord;
use App\Event\Issue\Event\RemoveIssueDependencyEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;

readonly class RemoveIssueDependencyEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private IssueDependencyEventRenderer $renderer,
        private RendererUrlGenerator $urlGenerator,
    ) {
    }

    public function eventDataClass(): string
    {
        return RemoveIssueDependencyEvent::class;
    }

    /**
     * @param array $events
     * @return EventRecord[]
     */
    public function fetch(array $events): array
    {
        return $this->renderer->fetchRecords($events, $this->render(...));
    }

    public function fetchForIssue(array $events): array
    {
        return $this->renderer->fetchRecords($events, $this->renderIssue(...));
    }

    private function renderIssue(Event $event, Issue $issue, Issue $dependency): string
    {
        $user = $event->getCreatedBy();

        $dependencyUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $dependency->getCode()
        ]);

        return sprintf(
            '<b>%s</b> has removed dependency <a href="%s">%s</a>',
            $user->getFullName(),
            $dependencyUrl,
            $dependency->getShortTitle()
        );
    }

    private function render(Event $event, Issue $issue, Issue $dependency): string
    {
        $user = $event->getCreatedBy();

        $issueUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode()
        ]);

        $dependencyUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $dependency->getCode()
        ]);

        return sprintf(
            '<b>%s</b> has removed from <a href="%s">issue</a> dependency <a href="%s">%s</a>',
            $user->getFullName(),
            $issueUrl,
            $dependencyUrl,
            $dependency->getShortTitle()
        );
    }
}
