<?php

namespace App\Action\User;

use App\Entity\User\User;
use App\Enum\User\UserStatusEnum;
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

        $user->setPasswordHash(null);
        $user->setStatusId(UserStatusEnum::InActive);

        $this->userRepository->flush();
    }
}
