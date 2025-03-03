<?php

namespace App\Enum\Issue;

enum IssueColumnEnum: int
{
    case Backlog = 1;

    case ToDo = 2;

    case InProgress = 3;

    case Test = 4;

    case InTests = 5;

    case Done = 6;

    case Closed = 7;

    case Archived = 8;

    public function label(): string
    {
        return match ($this) {
            IssueColumnEnum::Backlog => 'Backlog',
            IssueColumnEnum::ToDo => 'To do',
            IssueColumnEnum::InProgress => 'In progress',
            IssueColumnEnum::Test => 'Test',
            IssueColumnEnum::InTests => 'In tests',
            IssueColumnEnum::Done => 'Done',
            IssueColumnEnum::Closed => 'Closed',
            IssueColumnEnum::Archived => 'Archived',
        };
    }

}
