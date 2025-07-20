<?php

namespace App\Formulate\FormData;

use App\Formulate\FormField;

interface FormDataInterface
{

    public function get(): mixed;

    public function loadField(FormField $field): void;
}
