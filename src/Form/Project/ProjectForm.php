<?php

namespace App\Form\Project;

use App\Entity\Project\ProjectType;

class ProjectForm
{
    public function __construct(
        public ?string $name = null,
        public ?string $code = null,
        public ?ProjectType $type = null,
    ) {
    }

}
