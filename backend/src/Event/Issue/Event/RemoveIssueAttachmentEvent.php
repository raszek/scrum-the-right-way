<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class RemoveIssueAttachmentEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public string $fileName,
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'fileName' => $this->fileName
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            fileName: $data['fileName']
        );
    }

    public function name(): string
    {
        return IssueEventList::REMOVE_ISSUE_ATTACHMENT;
    }
}
