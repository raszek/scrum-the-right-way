<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class SetIssueStoryPointsEvent implements EventInterface
{

    public function __construct(
        public int $issueId,
        public ?int $storyPoints
    ) {
    }

    public function toArray(): array
    {
        return [
            'issueId' => $this->issueId,
            'storyPoints' => $this->storyPoints
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            issueId: $data['issueId'],
            storyPoints: $data['storyPoints']
        );
    }

    public function name(): string
    {
        return IssueEventList::SET_ISSUE_STORY_POINTS;
    }
}
