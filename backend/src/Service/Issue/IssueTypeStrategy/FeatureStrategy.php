<?php

namespace App\Service\Issue\IssueTypeStrategy;

use App\Entity\Issue\Issue;

readonly class FeatureStrategy implements IssueTypeStrategy
{
    public function __construct(
        private Issue $issue
    ) {
    }

    public function isEstimated(): bool
    {
        $nonEstimatedSubIssue = $this->issue
            ->getSubIssues()
            ->findFirst(fn(int $index, Issue $subIssue) => $subIssue->getStoryPoints() === null);

        return $nonEstimatedSubIssue === null;
    }

    public function countEstimated(): string
    {
        $estimatedCount = $this->issue
            ->getSubIssues()
            ->filter(fn(Issue $subIssue) => $subIssue->getStoryPoints() !== null)
            ->count();

        return sprintf('%d/%d', $estimatedCount, $this->issue->getSubIssues()->count());
    }
}
