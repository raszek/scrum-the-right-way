<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

readonly class GreaterThan implements FieldValidator
{

    public function __construct(
        private int $value,
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        if ($field->value() < $this->value) {
            return new FormFieldError(
                sprintf(
                    'Field %s is smaller than %d.',
                    $field->label(),
                    $this->value
                )
            );
        }

        return null;
    }
}
