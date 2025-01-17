<?php

namespace App\View\Issue;

readonly class SelectedTag
{

    public function __construct(
        public string $name,
        public bool $isSelected,
        public string $backgroundColor
    ) {
    }

}
