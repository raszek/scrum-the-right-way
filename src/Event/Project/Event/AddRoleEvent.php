<?php

namespace App\Event\Project\Event;

use App\Event\EventInterface;
use App\Event\Project\ProjectEventList;

readonly class AddRoleEvent extends RoleEvent implements EventInterface
{
    public function name(): string
    {
        return ProjectEventList::PROJECT_MEMBER_ADD_ROLE;
    }
}
