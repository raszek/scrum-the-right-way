<?php

namespace App\Formulate\Validator;

use Closure;

class ValidatorFactory
{
    public function notBlank(): NotBlank
    {
        return new NotBlank();
    }

    public function date(?string $format = null): Date
    {
        return $format ? new Date($format) : new Date();
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

    public function fileExtension(array $extensions): FileExtension
    {
        return new FileExtension($extensions);
    }

    public function fileSize(int $maxBytes): MaxFileSize
    {
        return new MaxFileSize($maxBytes);
    }

}
