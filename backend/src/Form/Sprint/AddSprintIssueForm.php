<?php

namespace App\Form\Sprint;

use Symfony\Component\Validator\Constraints\NotBlank;

class AddSprintIssueForm
{

    public function __construct(
        #[NotBlank]
        public ?array $issueIds = null
    ) {
    }

}
