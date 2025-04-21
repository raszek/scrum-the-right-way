<?php

namespace App\Service\Issue\IssueTypeStrategy;

use App\Entity\Issue\Issue;

readonly class DefaultStrategy implements IssueTypeStrategy
{

    public function __construct(
        private Issue $issue
    ) {
    }

    public function isEstimated(): bool
    {
        return $this->issue->getStoryPoints() !== null;
    }

    public function countEstimated(): string
    {
        return $this->isEstimated() ? '1/1' : '0/1';
    }
}
