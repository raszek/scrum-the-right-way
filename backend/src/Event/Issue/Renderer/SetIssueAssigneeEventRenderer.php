<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Event\EventRecord;
use App\Event\Issue\Event\SetIssueAssigneeEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;
use App\Repository\User\UserRepository;

readonly class SetIssueAssigneeEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private IssueRepository $issueRepository,
        private RendererUrlGenerator $urlGenerator
    ) {
    }

    public function eventDataClass(): string
    {
        return SetIssueAssigneeEvent::class;
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

    private function renderIssue(Event $event, Issue $issue, ?User $assignee): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has assigned issue to user <b>%s</b>',
            $user->getFullName(),
            $assignee ? $assignee->getFullName() : 'None'
        );
    }

    private function render(Event $event, Issue $issue, ?User $assignee): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has assigned issue <a href="%s">%s</a> to user <b>%s</b>',
            $user->getFullName(),
            $this->urlGenerator->generate('app_project_issue_view', [
                'id' => $event->getProject()->getId(),
                'issueCode' => $issue->getCode()
            ]),
            $issue->getShortTitle(),
            $assignee ? $assignee->getFullName() : 'None'
        );
    }

    private function fetchRecords(array $events, callable $render): array
    {
        $userIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->userId);
        $mappedUsers = $this->userRepository->mappedUsers($userIds);

        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        return ArrayHelper::map($events, function (Event $event) use ($mappedUsers, $mappedIssues, $render) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $mappedUsers[$event->getData()->userId] ?? null
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }
}
