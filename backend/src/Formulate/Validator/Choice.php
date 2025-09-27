<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldError;
use App\Formulate\FormFieldErrorInterface;

readonly class Choice implements FieldValidator
{

    /**
     * @param string[] $choices
     */
    public function __construct(
        private array $choices
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        if (in_array($field->value(), $this->choices)) {
            return null;
        }

        return new FormFieldError(
            sprintf(
                'Invalid value %s. Valid options are: [%s]',
                $field->value(),
                implode(', ', $this->choices)
            )
        );
    }
}
