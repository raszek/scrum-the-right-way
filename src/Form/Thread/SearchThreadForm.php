<?php

namespace App\Form\Thread;

use App\Enum\Thread\ThreadStatusEnum;

class SearchThreadForm
{

    public function __construct(
        public ?string $title = null,
        public ?ThreadStatusEnum $status = ThreadStatusEnum::Open,
    ) {
    }

}
