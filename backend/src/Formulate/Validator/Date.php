<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;

readonly class Date implements FieldValidator
{

    public function __construct(
        private string $format = 'Y-m-d'
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        try {
            CarbonImmutable::createFromFormat($this->format, $field->value());
        } catch (InvalidFormatException) {
            return new FormFieldError(
                sprintf('Field %s has invalid format. Date must be in format: %s',
                    $field->label(),
                    $this->format
                )
            );
        }

        return null;
    }
}
