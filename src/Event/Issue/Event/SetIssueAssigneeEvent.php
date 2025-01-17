<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class SetIssueAssigneeEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public ?int $userId,
    ) {
    }

    public function name(): string
    {
        return IssueEventList::SET_ISSUE_ASSIGNEE;
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'issueId' => $this->issueId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            userId: $data['userId']
        );
    }
}
