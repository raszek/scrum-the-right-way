<?php

namespace App\Action\User;

use App\Entity\User\User;
use App\Form\User\CommonUserForm;
use App\Repository\User\UserRepository;

readonly class UpdateUser
{

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function execute(CommonUserForm $form, User $user): void
    {
        $user->setFirstName($form->firstName);
        $user->setLastName($form->lastName);
        $user->setEmail($form->email);

        $this->userRepository->flush();
    }

}
