<?php

namespace App\Form\Project;

use App\Entity\Project\Project;
use Symfony\Component\Validator\Constraints as Assert;

class AddProjectMemberForm
{
    public function __construct(
        public Project $project,
        #[Assert\NotBlank()]
        public ?string $email = null
    ) {
    }

}
