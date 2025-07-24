<?php

namespace App\Form\Profile;

use App\Entity\User\User;

class ProfileFormData
{

    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
    }

    public static function fromUser(User $user): static
    {
        return new static(
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
        );
    }

}
