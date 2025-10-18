<?php

namespace App\Form\Issue;

class SortIssueFormData
{

    public function __construct(
        public ?int $position = null,
    ) {
    }

}
