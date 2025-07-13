<?php

namespace App\Table\User;

readonly class UserRecord
{

    public function __construct(
        public string $id,
        public string $email,
        public string $fullName
    ) {
    }

}
