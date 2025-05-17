<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Event\EventRecord;
use App\Event\Issue\Event\SetIssueAssigneeEvent;
use App\Event\Issue\Event\SetIssueStoryPointsEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;

readonly class SetIssueStoryPointsEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private IssueRepository $issueRepository,
        private RendererUrlGenerator $urlGenerator
    ) {
    }

    public function eventDataClass(): string
    {
        return SetIssueStoryPointsEvent::class;
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

    private function renderIssue(Event $event, Issue $issue, ?int $storyPoints): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has changed story points to %s',
            $user->getFullName(),
            $storyPoints ?? 'none'
        );
    }

    private function render(Event $event, Issue $issue, ?int $storyPoints): string
    {
        $user = $event->getCreatedBy();

        $issueUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode()
        ]);

        return sprintf(
            '<b>%s</b> has changed <a href="%s">issue</a> story points to %s',
            $user->getFullName(),
            $issueUrl,
            $storyPoints ?? 'none'
        );
    }

    private function fetchRecords(array $events, callable $render): array
    {
        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        return ArrayHelper::map($events, function (Event $event) use ($mappedIssues, $render) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $event->getData()->storyPoints
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }
}
