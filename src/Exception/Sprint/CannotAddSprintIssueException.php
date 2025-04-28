<?php

namespace App\Exception\Sprint;

use App\Entity\Issue\Issue;
use Exception;

class CannotAddSprintIssueException extends Exception
{

    public static function create(Issue $issue, string $message): static
    {
        return new static(sprintf('Cannot add issue %s to sprint: %s', $issue->getCode(), $message));
    }
}
