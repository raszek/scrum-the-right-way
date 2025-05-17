<?php

namespace App\Form\Site;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordForm
{

    public function __construct(
        #[Assert\NotBlank()]
        public $password = null,
        #[Assert\NotBlank()]
        public ?string $resetPasswordCode = null,
        #[Assert\NotBlank()]
        public ?string $email = null,
    ) {
    }

}
