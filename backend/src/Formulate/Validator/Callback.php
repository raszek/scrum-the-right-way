<?php

namespace App\Formulate\Validator;

use App\Formulate\FieldValidator;
use App\Formulate\Form;
use App\Formulate\FormField;
use App\Formulate\FormFieldErrorInterface;
use Closure;

readonly class Callback implements FieldValidator
{

    public function __construct(
        private Closure $callback
    ) {
    }

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface
    {
        return $this->callback->__invoke($field, $form);
    }
}
