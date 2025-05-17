<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class AddIssueThreadMessageEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public int $threadMessageId
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'threadMessageId' => $this->threadMessageId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            threadMessageId: $data['threadMessageId']
        );
    }

    public function name(): string
    {
        return IssueEventList::ADD_ISSUE_THREAD_MESSAGE;
    }
}
