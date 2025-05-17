<?php

namespace App\Service\Common;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class FormValidator
{

    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    public function validate(mixed $form): void
    {
        $errors = $this->validator->validate($form);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new UnprocessableEntityHttpException($errorsString);
        }
    }


}
