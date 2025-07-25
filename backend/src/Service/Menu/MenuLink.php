<?php

namespace App\Service\Menu;

class MenuLink
{

    public function __construct(
        public string $label,
        public MenuUrl $url,
    ) {
    }

}
