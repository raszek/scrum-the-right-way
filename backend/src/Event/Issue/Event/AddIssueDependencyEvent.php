<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class AddIssueDependencyEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public int $dependencyId
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'dependencyId' => $this->dependencyId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            dependencyId: $data['dependencyId']
        );
    }

    public function name(): string
    {
        return IssueEventList::ADD_ISSUE_DEPENDENCY;
    }
}
