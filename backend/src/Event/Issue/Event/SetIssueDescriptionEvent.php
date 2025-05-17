<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class SetIssueDescriptionEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public int $historyId
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'historyId' => $this->historyId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            historyId: $data['historyId']
        );
    }

    public function name(): string
    {
        return IssueEventList::SET_ISSUE_DESCRIPTION;
    }
}
