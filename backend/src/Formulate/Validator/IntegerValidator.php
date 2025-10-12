<?php

namespace App\Formulate\Validator;


use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

class IntegerValidator implements FieldValidator
{

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        if (filter_var($field->value(), FILTER_VALIDATE_INT) === false) {
            return new FormFieldError(sprintf('Field %s is not a valid integer', $field->label()));
        }

        return null;
    }
}
