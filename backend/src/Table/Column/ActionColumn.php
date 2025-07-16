<?php

namespace App\Table\Column;

use App\Table\TableColumn;
use Closure;

class ActionColumn extends TableColumn
{

    public function __construct(
        string $field,
        string $label,
        ?string $sortField = null,
        ?string $stripTags = '<div><button><i><ul><li><a>',
        ?Closure $formatCallback = null
    ) {
        parent::__construct($field, $label, $sortField, $stripTags, $formatCallback);
    }

}
