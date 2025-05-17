<?php

namespace App\Form\Sprint;

use Symfony\Component\Validator\Constraints\NotBlank;

class SprintGoalIssueMoveForm
{

    public function __construct(
        #[NotBlank]
        public ?int $position = null,
        #[NotBlank]
        public ?string $goalId = null,
    ) {
    }

}
