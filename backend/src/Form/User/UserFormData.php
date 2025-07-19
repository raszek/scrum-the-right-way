<?php

namespace App\Form\User;

class UserFormData
{

    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
    }

}
