<?php

namespace App\Event;

use App\Event\Issue\IssueEventList;
use App\Event\Project\ProjectEventList;
use App\Event\Thread\ThreadEventList;

readonly class FullEventList
{

    /**
     * @return class-name<EventList>[]
     */
    public static function eventLists(): array
    {
        return [
            ThreadEventList::class,
            ProjectEventList::class,
            IssueEventList::class
        ];
    }

    public static function renderers(): array
    {
        $renderers = [];
        foreach (self::eventLists() as $eventList) {
            $renderers = array_merge($renderers, $eventList::rendererClasses());
        }

        return $renderers;
    }

    /**
     * @return array<string, string>
     */
    public function selections(): array
    {
        $result = [];

        foreach (self::eventLists() as $eventList) {
            $eventList = new $eventList;
            $result = array_merge($result, $eventList->labels());
        }

        return $result;
    }

}
