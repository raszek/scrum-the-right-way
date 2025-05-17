<?php

namespace App\Form\Sprint;

use App\Validator\Sprint\SprintEndDate;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class StartSprintForm
{

    public function __construct(
        #[Assert\NotBlank()]
        #[SprintEndDate]
        public ?DateTimeImmutable $estimatedEndDate = null,
    ) {
    }

}
