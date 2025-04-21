<?php

namespace App\Form\Sprint;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class StartSprintForm
{

    public function __construct(
        #[Assert\NotBlank()]
        #[Assert\GreaterThan('today')]
        public ?DateTimeImmutable $estimatedEndDate = null,
    ) {
    }

}
