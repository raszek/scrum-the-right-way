<?php

namespace App\Form\Sprint;

class SprintGoalIssueMoveForm
{

    public function __construct(
        public ?int $position = null,
        public ?string $goalId = null,
    ) {
    }

}
