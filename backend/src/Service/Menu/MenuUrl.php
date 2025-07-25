<?php

namespace App\Service\Menu;

readonly class MenuUrl
{

    public function __construct(
        public string $name,
        public array $params = []
    ) {
    }

}
