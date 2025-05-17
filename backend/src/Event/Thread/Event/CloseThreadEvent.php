<?php

namespace App\Event\Thread\Event;

use App\Event\EventInterface;
use App\Event\Thread\ThreadEventList;

readonly class CloseThreadEvent extends ThreadEvent implements EventInterface
{

    public function name(): string
    {
        return ThreadEventList::THREAD_CLOSE;
    }
}
