<?php

namespace App\Form\Site;

class ForgotPasswordFormData
{

    public function __construct(
        public ?string $email = null,
    ) {
    }

}
