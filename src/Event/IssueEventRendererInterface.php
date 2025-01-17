<?php

namespace App\Event;

use App\Entity\Event\Event;

interface IssueEventRendererInterface extends EventRendererInterface
{

    /**
     * @param Event[] $events
     * @return EventRecord[]
     */
    public function fetchForIssue(array $events): array;
}
