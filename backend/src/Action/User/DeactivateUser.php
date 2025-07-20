<?php

namespace App\Action\User;

use App\Entity\User\User;
use App\Repository\User\UserRepository;

readonly class DeactivateUser
{

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function execute(User $user): void
    {
        if (!$user->isActive()) {
            throw new \DomainException('User is already inactive');
        }

        $user->setActivationCode(null);
        $user->setResetPasswordCode(null);
        $user->setPasswordHash(null);

        $this->userRepository->flush();
    }
}
