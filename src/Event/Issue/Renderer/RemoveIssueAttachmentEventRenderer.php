<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Event\EventRecord;
use App\Event\Issue\Event\RemoveIssueAttachmentEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;

readonly class RemoveIssueAttachmentEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private IssueRepository $issueRepository,
        private RendererUrlGenerator $urlGenerator,
    ) {
    }

    public function eventDataClass(): string
    {
        return RemoveIssueAttachmentEvent::class;
    }

    /**
     * @param array $events
     * @return EventRecord[]
     */
    public function fetch(array $events): array
    {
        return $this->fetchRecords($events, $this->render(...));
    }

    public function fetchForIssue(array $events): array
    {
        return $this->fetchRecords($events, $this->renderIssue(...));
    }

    private function renderIssue(Event $event, Issue $issue, string $fileName): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has removed attachment %s',
            $user->getFullName(),
            $fileName
        );
    }

    private function render(Event $event, Issue $issue, string $fileName): string
    {
        $user = $event->getCreatedBy();

        $issueUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode()
        ]);

        return sprintf(
            '<b>%s</b> has removed from <a href="%s">issue</a> attachment %s',
            $user->getFullName(),
            $issueUrl,
            $fileName
        );
    }

    public function fetchRecords(array $events, callable $render): array
    {
        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        return ArrayHelper::map($events, function (Event $event) use (
            $mappedIssues,
            $render
        ) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $event->getData()->fileName
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }

}
