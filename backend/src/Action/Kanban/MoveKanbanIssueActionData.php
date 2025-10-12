<?php

namespace App\Action\Kanban;

use App\Entity\Issue\Issue;
use App\Entity\User\User;

readonly class MoveKanbanIssueActionData
{

    public function __construct(
        public Issue $issue,
        public User $user,
        public int $position,
        public string $column
    ) {
    }

}
