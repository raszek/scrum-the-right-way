<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

readonly class Regex implements FieldValidator
{

    public function __construct(
        private string $regex
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        $matches = preg_match($this->regex, $field->value());

        if ($matches === 1) {
            return null;
        }

        return new FormFieldError(
            sprintf(
                'Text "%s" does not match regex with pattern %s',
                $field->value(),
                $this->regex
            )
        );
    }
}
