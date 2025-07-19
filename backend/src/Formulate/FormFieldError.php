<?php

namespace App\Formulate;

readonly class FormFieldError implements FormFieldErrorInterface
{

    public function __construct(
        private string $message
    ) {
    }

    public function message(): string
    {
        return $this->message;
    }
}
