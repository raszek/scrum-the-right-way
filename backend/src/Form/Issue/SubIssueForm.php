<?php

namespace App\Form\Issue;

use Symfony\Component\HttpFoundation\Request;

class SubIssueForm
{

    public function __construct(
        public ?string $title = null,
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            title: $request->get('title'),
        );
    }

}
