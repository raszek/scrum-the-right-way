<?php

namespace App\Form\Project;

class ProjectFormData
{
    public function __construct(
        public ?string $name = null,
        public ?string $code = null,
        public ?string $type = null,
    ) {
    }

}
