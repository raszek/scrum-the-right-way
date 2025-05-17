<?php

namespace App\Event\Project\Event;

use App\Event\EventInterface;
use App\Event\Project\ProjectEventList;

readonly class RemoveRoleEvent extends RoleEvent implements EventInterface
{

    public function name(): string
    {
        return ProjectEventList::PROJECT_MEMBER_REMOVE_ROLE;
    }
}
