<?php

namespace App\Event\Project\Event;

use App\Event\EventInterface;
use App\Event\Project\ProjectEventList;

readonly class AddMemberEvent extends MemberEvent implements EventInterface
{

    public function name(): string
    {
        return ProjectEventList::PROJECT_ADD_MEMBER;
    }
}
