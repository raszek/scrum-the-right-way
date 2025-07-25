<?php

namespace App\Form\Profile;

class ChangePasswordFormData
{

    public function __construct(
        public ?string $currentPassword = null,
        public ?string $newPassword = null,
        public ?string $repeatPassword = null,
    ) {
    }

}
