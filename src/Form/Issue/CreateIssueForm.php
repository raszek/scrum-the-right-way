<?php

namespace App\Form\Issue;

use App\Entity\Issue\IssueType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateIssueForm
{

    public function __construct(
        #[Assert\NotBlank()]
        public ?string $title = null,
        #[Assert\NotBlank()]
        public ?IssueType $type = null,
    ) {
    }

}
