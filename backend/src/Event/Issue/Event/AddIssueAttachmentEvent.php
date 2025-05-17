<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class AddIssueAttachmentEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public int $attachmentId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'attachmentId' => $this->attachmentId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            attachmentId: $data['attachmentId']
        );
    }

    public function name(): string
    {
        return IssueEventList::ADD_ISSUE_ATTACHMENT;
    }
}
