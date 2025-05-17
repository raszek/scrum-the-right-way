<?php

namespace App\Enum\Issue;

enum IssueTypeEnum: int
{
    case Issue = 1;

    case Feature = 2;

    case SubIssue = 3;

    public function label(): string
    {
        return match ($this) {
            IssueTypeEnum::Issue => 'Issue',
            IssueTypeEnum::Feature => 'Feature',
            IssueTypeEnum::SubIssue => 'Sub issue',
        };
    }

    /**
     * @return IssueTypeEnum[]
     */
    public static function createTypes(): array
    {
        return [
            IssueTypeEnum::Issue,
            IssueTypeEnum::Feature,
        ];
    }
}
