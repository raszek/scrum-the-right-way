<?php

namespace App\View\Kanban;

use App\Entity\Issue\Issue;

readonly class KanbanColumn
{

    public function __construct(
        public string $name,
        public string $key,
        /**
         * @var Issue[]
         */
        public array $items
    ) {
    }

}
