<?php

namespace App\Service\Issue\IssueTypeStrategy;

interface IssueTypeStrategy
{

    public function isEstimated(): bool;

    public function countEstimated(): string;
}
