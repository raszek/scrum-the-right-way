<?php

namespace App\Form\Site;

class ResetPasswordFormData
{

    public function __construct(
        public ?string $password = null,
        public ?string $repeatPassword = null,
        public ?string $resetPasswordCode = null,
        public ?string $email = null,
    ) {
    }

}
