<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Event\Issue\Event\IssueEvent;
use App\Event\IssueEventRendererInterface;

readonly class CreateIssueEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private IssueEventRenderer $issueEventRenderer,
    ) {
    }

    public function fetch(array $events): array
    {
        return $this->issueEventRenderer->fetch($events, $this->render(...));
    }

    public function fetchForIssue(array $events): array
    {
        return $this->issueEventRenderer->fetch($events, $this->renderIssue(...));
    }

    public function renderIssue(Event $event): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has created issue',
            $user->getFullName(),
        );
    }

    private function render(Event $event, Issue $issue): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has created issue <a href="%s">%s</a>',
            $user->getFullName(),
            $this->generateUrl($issue),
            $issue->getShortTitle()
        );
    }

    private function generateUrl(Issue $issue): string
    {
        return sprintf(
            '/projects/%s/backlog/issues/%s',
            $issue->getProject()->getId(),
            $issue->getCode()
        );
    }

    public function eventDataClass(): string
    {
        return IssueEvent::class;
    }
}
