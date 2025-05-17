<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class SetIssueTagsEvent implements EventInterface
{

    /**
     * @param int $issueId
     * @param string[] $tags
     */
    public function __construct(
        public int $issueId,
        public array $tags
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'tags' => $this->tags
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            tags: $data['tags']
        );
    }

    public function name(): string
    {
        return IssueEventList::SET_ISSUE_TAGS;
    }
}
