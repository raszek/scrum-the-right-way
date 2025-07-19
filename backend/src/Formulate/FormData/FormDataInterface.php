<?php

namespace App\Formulate\FormData;

interface FormDataInterface
{

    public function get(): mixed;

    public function load(): void;
}
