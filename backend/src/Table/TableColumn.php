<?php

namespace App\Table;

use Closure;

class TableColumn
{

    public function __construct(
        public string $field,
        public string $label,
        public ?string $sortField = null,
        public ?string $stripTags = null,
        public ?Closure $formatCallback = null,
    ) {
    }

}
