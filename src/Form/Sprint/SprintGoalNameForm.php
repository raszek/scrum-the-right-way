<?php

namespace App\Form\Sprint;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

readonly class SprintGoalNameForm
{

    public function __construct(
        #[NotBlank]
        #[Length(min: 1, max: 255)]
        public ?string $name = null
    ) {
    }

}
