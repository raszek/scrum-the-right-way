<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Entity\Thread\ThreadMessage;
use App\Event\EventRecord;
use App\Event\Issue\Event\AddIssueThreadMessageEvent;
use App\Event\Issue\Event\SetIssueAssigneeEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;
use App\Repository\Thread\ThreadMessageRepository;

readonly class AddIssueThreadMessageEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private IssueRepository $issueRepository,
        private ThreadMessageRepository $threadMessageRepository,
        private RendererUrlGenerator $urlGenerator,
    ) {
    }

    public function eventDataClass(): string
    {
        return AddIssueThreadMessageEvent::class;
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

    private function renderIssue(Event $event, Issue $issue, ThreadMessage $threadMessage): string
    {
        $user = $event->getCreatedBy();

        $threadUrl = $this->urlGenerator->generate('app_project_thread_messages', [
            'id' => $event->getProject()->getId(),
            'threadId' => $threadMessage->getThread()->getId(),
            'slug' => $threadMessage->getThread()->getSlug(),
        ]).'#'.$threadMessage->getNumber();

        return sprintf(
            '<b>%s</b> has added thread message <a href="%s">%s</a>',
            $user->getFullName(),
            $threadUrl,
            $threadMessage->getIssueTitle()
        );
    }

    private function render(Event $event, Issue $issue, ThreadMessage $threadMessage): string
    {
        $user = $event->getCreatedBy();

        $issueUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode()
        ]);

        $threadUrl = $this->urlGenerator->generate('app_project_thread_messages', [
            'id' => $event->getProject()->getId(),
            'threadId' => $threadMessage->getThread()->getId(),
            'slug' => $threadMessage->getThread()->getSlug(),
        ]).'#'.$threadMessage->getNumber();

        return sprintf(
            '<b>%s</b> has added to <a href="%s">issue</a> thread message <a href="%s">%s</a>',
            $user->getFullName(),
            $issueUrl,
            $threadUrl,
            $threadMessage->getIssueTitle()
        );
    }

    private function fetchRecords(array $events, callable $render): array
    {
        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        $messageIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->threadMessageId);
        $mappedMessages = $this->threadMessageRepository->mappedMessages($messageIds);

        return ArrayHelper::map($events, function (Event $event) use (
            $mappedIssues,
            $mappedMessages,
            $render
        ) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $mappedMessages[$event->getData()->threadMessageId]
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }
}
