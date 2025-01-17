<?php

namespace App\Form\Event;

use App\Entity\Project\Project;
use App\Entity\User\User;

class SearchEventForm
{

    public function __construct(
        public readonly Project $project,
        public ?string $name = null,
        public ?User $createdBy = null
    ) {
    }

}
