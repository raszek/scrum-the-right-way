<?php

namespace App\Formulate;

interface FieldValidator
{

    public function validate(FormField $field, Form $form): ?FormFieldErrorInterface;

}
