<?php

namespace App\Action\User;

use App\Entity\User\User;
use App\Form\User\CreateUserForm;
use App\Repository\User\UserRepository;
use App\Service\Common\ClockInterface;
use App\Service\Site\CreateUserEmail;
use Symfony\Component\String\ByteString;

readonly class CreateUser
{

    public function __construct(
        private ClockInterface $clock,
        private UserRepository $userRepository,
        private CreateUserEmail $registerMail,
    ) {
    }

    public function execute(CreateUserForm $form): User
    {
        $user = new User(
            email: $form->email,
            firstName: $form->firstName,
            lastName: $form->lastName,
            createdAt: $this->clock->now(),
            activationCode: ByteString::fromRandom(64)->toString()
        );

        $this->userRepository->create($user);

        $this->registerMail->send($user);

        return $user;
    }

}
