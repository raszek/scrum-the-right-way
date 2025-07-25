<?php

namespace App\Service\Menu;

readonly class MenuLinkRecord
{

    public function __construct(
        public string $label,
        public string $path,
        public bool $isActive,
    ) {
    }

    public function htmlClass(): string
    {
        return $this->isActive ? 'active' : '';
    }
}
