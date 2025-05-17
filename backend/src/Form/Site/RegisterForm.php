<?php

namespace App\Form\Site;

use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('email', entityClass: User::class)]
class RegisterForm
{

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email = null,

        #[Assert\NotBlank]
        public ?string $firstName = null,

        #[Assert\NotBlank]
        public ?string $lastName = null,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public $password = null,
    ) {
    }

}
