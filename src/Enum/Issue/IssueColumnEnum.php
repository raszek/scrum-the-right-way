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

    public function key(): string
    {
        return match ($this) {
            IssueColumnEnum::Backlog => 'backlog',
            IssueColumnEnum::ToDo => 'to-do',
            IssueColumnEnum::InProgress => 'in-progress',
            IssueColumnEnum::Test => 'test',
            IssueColumnEnum::InTests => 'in-tests',
            IssueColumnEnum::Done => 'done',
            IssueColumnEnum::Closed => 'closed',
            IssueColumnEnum::Archived => 'archived',
        };
    }

    public static function fromKey(string $key): IssueColumnEnum
    {
        return match ($key) {
            'backlog' => IssueColumnEnum::Backlog,
            'to-do' => IssueColumnEnum::ToDo,
            'in-progress' => IssueColumnEnum::InProgress,
            'test' => IssueColumnEnum::Test,
            'in-tests' => IssueColumnEnum::InTests,
            'done' => IssueColumnEnum::Done,
            'closed' => IssueColumnEnum::Closed,
            'archived' => IssueColumnEnum::Archived,
        };
    }

    /**
     * @return IssueColumnEnum[]
     */
    public static function kanbanColumns(): array
    {
        return [
            IssueColumnEnum::ToDo,
            IssueColumnEnum::InProgress,
            IssueColumnEnum::Test,
            IssueColumnEnum::InTests,
            IssueColumnEnum::Done,
        ];
    }

    public function isInProgress(): bool
    {
        return $this === IssueColumnEnum::InProgress;
    }

    public function isInTests(): bool
    {
        return $this === IssueColumnEnum::InTests;
    }
}
