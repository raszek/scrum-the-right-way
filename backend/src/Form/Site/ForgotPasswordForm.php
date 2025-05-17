<?php

namespace App\Form\Site;

use Symfony\Component\Validator\Constraints as Assert;

class ForgotPasswordForm
{

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email = null,
    ) {
    }

}
