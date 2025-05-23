<?php

namespace App\Event\Thread\Event;

use App\Event\EventInterface;
use App\Event\Thread\ThreadEventList;

readonly class AddThreadMessageEvent extends ThreadEvent implements EventInterface
{

    public function name(): string
    {
        return ThreadEventList::THREAD_ADD_MESSAGE;
    }
}
