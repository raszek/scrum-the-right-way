<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

readonly class Repeat implements FieldValidator
{

    public function __construct(
        private string $fieldName
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        $repeatedField = $form->findField($this->fieldName);

        if ($field->value() !== $repeatedField->value()) {
            return new FormFieldError(sprintf('%s does not match %s', $field->label(), $repeatedField->label()));
        }

        return null;
    }
}
