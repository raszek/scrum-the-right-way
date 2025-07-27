<?php

namespace App\Formulate\Validator;

use Closure;

class ValidatorFactory
{
    public function notBlank(): NotBlank
    {
        return new NotBlank();
    }

    public function email(): Email
    {
        return new Email();
    }

    public function callback(Closure $closure): Callback
    {
        return new Callback($closure);
    }

    public function repeat(string $fieldName): Repeat
    {
        return new Repeat($fieldName);
    }

}
