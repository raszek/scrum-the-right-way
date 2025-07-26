<?php

namespace App\Form\Profile;

use App\Entity\User\User;

class ChangeEmailFormData
{

    public function __construct(
        public ?string $email = null,
    ) {
    }

    public static function fromUser(User $user): static
    {
        return new static(
            email: $user->getEmail(),
        );
    }
}
