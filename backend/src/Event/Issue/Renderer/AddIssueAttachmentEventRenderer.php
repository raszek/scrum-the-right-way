<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Attachment;
use App\Entity\Issue\Issue;
use App\Event\EventRecord;
use App\Event\Issue\Event\AddIssueAttachmentEvent;
use App\Event\IssueEventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Helper\ArrayHelper;
use App\Repository\Issue\AttachmentRepository;
use App\Repository\Issue\IssueRepository;

readonly class AddIssueAttachmentEventRenderer implements IssueEventRendererInterface
{
    public function __construct(
        private IssueRepository $issueRepository,
        private AttachmentRepository $attachmentRepository,
        private RendererUrlGenerator $urlGenerator,
    ) {
    }

    public function eventDataClass(): string
    {
        return AddIssueAttachmentEvent::class;
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

    private function renderIssue(Event $event, Issue $issue, ?Attachment $attachment): string
    {
        $user = $event->getCreatedBy();

        if (!$attachment) {
            return sprintf(
                '<b>%s</b> has added attachment (removed)',
                $user->getFullName(),
            );
        }

        $attachmentUrl = $this->urlGenerator->generate('app_project_issue_view_attachment', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode(),
            'attachmentId' => $attachment->getId()
        ]);

        return sprintf(
            '<b>%s</b> has added attachment <a href="%s">%s</a>',
            $user->getFullName(),
            $attachmentUrl,
            $attachment->getFile()->getName()
        );
    }

    private function render(Event $event, Issue $issue, ?Attachment $attachment): string
    {
        $user = $event->getCreatedBy();

        $issueUrl = $this->urlGenerator->generate('app_project_issue_view', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode()
        ]);

        if (!$attachment) {
            return sprintf(
                '<b>%s</b> has added to <a href="%s">issue</a> attachment (removed)',
                $user->getFullName(),
                $issueUrl,
            );
        }

        $attachmentUrl = $this->urlGenerator->generate('app_project_issue_view_attachment', [
            'id' => $event->getProject()->getId(),
            'issueCode' => $issue->getCode(),
            'attachmentId' => $attachment->getId()
        ]);

        return sprintf(
            '<b>%s</b> has added to <a href="%s">issue</a> attachment <a href="%s">%s</a>',
            $user->getFullName(),
            $issueUrl,
            $attachmentUrl,
            $attachment->getFile()->getName()
        );
    }

    public function fetchRecords(array $events, callable $render): array
    {
        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        $attachmentIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->attachmentId);
        $mappedAttachments = $this->attachmentRepository->mappedAttachments($attachmentIds);

        return ArrayHelper::map($events, function (Event $event) use (
            $mappedIssues,
            $mappedAttachments,
            $render
        ) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $mappedAttachments[$event->getData()->attachmentId]
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }

}
