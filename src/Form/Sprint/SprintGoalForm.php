<?php

namespace App\Form\Sprint;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SprintGoalForm
{

    public function __construct(
        #[NotBlank]
        #[Length(max: 255)]
        public ?string $name = null,
    ) {
    }

}
