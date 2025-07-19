<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

class Email implements FieldValidator
{

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        if (!filter_var($field->value(), FILTER_VALIDATE_EMAIL)) {
            return new FormFieldError(sprintf('%s field must be a valid email address', $field->label()));
        }

        return null;
    }
}
