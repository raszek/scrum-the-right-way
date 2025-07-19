<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

class NotBlank implements FieldValidator
{

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        if (!$field->value()) {
            return new FormFieldError(sprintf('%s cannot be blank', $field->label ?? $field->name));
        }

        return null;
    }
}
