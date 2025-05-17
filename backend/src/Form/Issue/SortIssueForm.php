<?php

namespace App\Form\Issue;

use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

readonly class SortIssueForm
{

    public function __construct(
        #[NotBlank]
        #[Type('integer')]
        #[GreaterThan(0)]
        public string $position,
    ) {
    }

}
