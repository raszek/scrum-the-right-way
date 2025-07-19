<?php

namespace App\Formulate;

interface FormWidget
{
    public function render(FormField $formField, Form $form): string;
}
