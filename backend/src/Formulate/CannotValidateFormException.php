<?php

namespace App\Formulate;

use App\Helper\JsonHelper;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CannotValidateFormException extends UnprocessableEntityHttpException
{

    public function __construct(
        private readonly Form $form,
    ) {
        parent::__construct($this->serialize());
    }

    private function serialize(): string
    {
        return JsonHelper::encode($this->form->getErrors());
    }
}
