<?php

namespace App\Form\Position;

use Symfony\Component\Validator\Constraints\NotBlank;

readonly class PositionForm
{

    public function __construct(
        #[NotBlank]
        public ?int $position = null,
    ) {
    }

}
