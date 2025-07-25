<?php

namespace App\Service\Menu;

readonly class Menu
{
    public function __construct(
        public array $links = []
    ) {
    }
}
