<?php

namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('email', entityClass: User::class)]
class CreateUserForm
{

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email = null,

        #[Assert\NotBlank]
        public ?string $firstName = null,

        #[Assert\NotBlank]
        public ?string $lastName = null,
    ) {
    }

}
