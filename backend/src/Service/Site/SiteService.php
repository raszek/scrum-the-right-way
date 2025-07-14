<?php

namespace App\Service\Site;

use App\Entity\User\User;
use App\Exception\Site\UserNotFoundException;
use App\Form\Site\ResetPasswordForm;
use App\Form\User\CreateUserForm;
use App\Repository\User\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

readonly class SiteService
{

    public function __construct(
        private UserRepository $userRepository,
        private ForgotPasswordMail $forgotPasswordMail,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function activateUser(User $user): void
    {
        $user->setActivationCode(null);
    }

    public function setResetPasswordCode(User $user): void
    {
        $user->setResetPasswordCode(ByteString::fromRandom(64)->toString());

        $this->forgotPasswordMail->send($user);
    }

    public function resetPassword(ResetPasswordForm $resetPasswordForm): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $resetPasswordForm->email,
            'resetPasswordCode' => $resetPasswordForm->resetPasswordCode,
        ]);

        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        $passwordHash = $this->userPasswordHasher->hashPassword($user, $resetPasswordForm->password);

        $user->setPasswordHash($passwordHash);
        $user->setResetPasswordCode(null);
    }
}
