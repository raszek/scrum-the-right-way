<?php

namespace App\Form\Thread;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class ThreadForm
{

    public function __construct(
        public Project $project,
        public User $createdBy,
        #[Assert\NotBlank()]
        #[Assert\Length(max: 200)]
        public ?string $title = null,
        #[Assert\NotBlank()]
        #[Assert\Length(max: 10000)]
        public ?string $message = null,
    ) {
    }

}
