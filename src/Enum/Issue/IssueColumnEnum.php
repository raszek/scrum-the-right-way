<?php

namespace App\Enum\Issue;

enum IssueColumnEnum: int
{
    case Backlog = 1;

    case ToDo = 2;

    case InProgress = 3;

    case Test = 4;

    case Tested = 5;

    case Done = 6;

    public function label(): string
    {
        return match ($this) {
            IssueColumnEnum::Backlog => 'Backlog',
            IssueColumnEnum::ToDo => 'To do',
            IssueColumnEnum::InProgress => 'In progress',
            IssueColumnEnum::Test => 'Test',
            IssueColumnEnum::Tested => 'Tested',
            IssueColumnEnum::Done => 'Done'
        };
    }

}
