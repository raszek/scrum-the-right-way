<?php

namespace App\Form\Thread;

use Symfony\Component\Validator\Constraints as Assert;

class MessageForm
{

    public function __construct(
        #[Assert\NotBlank()]
        public ?string $content = null
    ) {
    }

}
