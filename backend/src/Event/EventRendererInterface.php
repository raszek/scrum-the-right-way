<?php

namespace App\Event;

use App\Entity\Event\Event;

interface EventRendererInterface
{

    /**
     * @param Event[] $events
     * @return EventRecord[]
     */
    public function fetch(array $events): array;

    /**
     * @return class-string<EventInterface>
     */
    public function eventDataClass(): string;
}
