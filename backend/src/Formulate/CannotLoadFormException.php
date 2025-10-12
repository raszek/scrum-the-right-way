<?php

namespace App\Formulate;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CannotLoadFormException extends UnprocessableEntityHttpException
{
    public function __construct() {
        parent::__construct('Cannot load form');
    }
}
