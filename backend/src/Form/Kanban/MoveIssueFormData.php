<?php

namespace App\Form\Kanban;

class MoveIssueFormData
{
    public function __construct(
        public ?int $position = null,
        public ?string $column = null,
    ) {
    }

}
