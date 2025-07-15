<?php

namespace App\Action\Site;

use App\Form\Site\ResetPasswordForm;
use App\Repository\User\UserRepository;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ActivateUser
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function execute(ResetPasswordForm $form): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $form->email,
            'activationCode' => $form->resetPasswordCode,
            'passwordHash' => null
        ]);

        if (!$user) {
            throw new DomainException('User not found');
        }

        $user->setPasswordHash($this->userPasswordHasher->hashPassword($user, $form->password));
        $user->setResetPasswordCode(null);

        $this->userRepository->flush();
    }
}
