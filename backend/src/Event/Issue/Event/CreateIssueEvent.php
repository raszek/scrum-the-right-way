<?php

namespace App\Event\Issue\Event;

use App\Event\EventInterface;
use App\Event\Issue\IssueEventList;

readonly class CreateIssueEvent extends IssueEvent implements EventInterface
{

    public function name(): string
    {
        return IssueEventList::CREATE_ISSUE;
    }
}
