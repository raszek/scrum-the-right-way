<?php

namespace App\Event\Issue\Event;

readonly class IssueEvent
{

    public function __construct(
        public int $issueId
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId']
        );
    }
}
