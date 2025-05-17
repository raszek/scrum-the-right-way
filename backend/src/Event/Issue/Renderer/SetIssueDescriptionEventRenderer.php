<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Event\EventRecord;
use App\Event\Issue\Event\SetIssueAssigneeEvent;
use App\Event\Issue\Event\SetIssueDescriptionEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Helper\ArrayHelper;
use App\Repository\Issue\DescriptionHistoryRepository;
use App\Repository\Issue\IssueRepository;

readonly class SetIssueDescriptionEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private DescriptionHistoryRepository $descriptionHistoryRepository,
        private IssueRepository $issueRepository,
        private RendererUrlGenerator $urlGenerator
    ) {
    }

    public function eventDataClass(): string
    {
        return SetIssueDescriptionEvent::class;
    }

    /**
     * @param Event<SetIssueAssigneeEvent>[] $events
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

    private function renderIssue(Event $event, Issue $issue, DescriptionHistory $descriptionHistory): string
    {
        $user = $event->getCreatedBy();

        $diffUrl = $this->urlGenerator->generate('app_issue_description_history_show', [
            'id' => $issue->getProject()->getId(),
            'issueCode' => $issue->getCode(),
            'historyId' => $descriptionHistory->getId()
        ]);

        return sprintf(
            '<b>%s</b> has changed issue description (<a href="%s" target="_blank">diff</a>)',
            $user->getFullName(),
            $diffUrl
        );
    }

    private function render(Event $event, Issue $issue, DescriptionHistory $history): string
    {
        $user = $event->getCreatedBy();

        $issueUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode()
        ]);

        $diffUrl = $this->urlGenerator->generate('app_issue_description_history_show', [
            'id' => $issue->getProject()->getId(),
            'issueCode' => $issue->getCode(),
            'historyId' => $history->getId()
        ]);

        return sprintf(
            '<b>%s</b> has changed <a href="%s">issue</a> description (<a href="%s" target="_blank">diff</a>)',
            $user->getFullName(),
            $issueUrl,
            $diffUrl
        );
    }

    private function fetchRecords(array $events, callable $render): array
    {
        $historyIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->historyId);
        $mappedHistories = $this->descriptionHistoryRepository->mappedHistories($historyIds);

        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        return ArrayHelper::map($events, function (Event $event) use ($mappedHistories, $mappedIssues, $render) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $mappedHistories[$event->getData()->historyId]
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }
}
