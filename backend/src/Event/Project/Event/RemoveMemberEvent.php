<?php

namespace App\Event\Project\Event;

use App\Event\EventInterface;
use App\Event\Project\ProjectEventList;

readonly class RemoveMemberEvent extends MemberEvent implements EventInterface
{

    public function name(): string
    {
        return ProjectEventList::PROJECT_REMOVE_MEMBER;
    }
}
