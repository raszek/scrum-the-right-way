<?php

namespace App\Service\Site;

use App\Entity\User\User;
use App\Exception\Site\UserNotFoundException;
use App\Form\Site\RegisterForm;
use App\Form\Site\ResetPasswordForm;
use App\Repository\User\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

readonly class SiteService
{

    public function __construct(
        private UserRepository $userRepository,
        private RegisterMail $registerMail,
        private ForgotPasswordMail $forgotPasswordMail,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function register(RegisterForm $registerForm): void
    {
        $user = new User(
            email: $registerForm->email,
            plainPassword: $registerForm->password,
            firstName: $registerForm->firstName,
            lastName: $registerForm->lastName,
            createdAt: new \DateTimeImmutable(),
            activationCode: ByteString::fromRandom(64)->toString()
        );

        $this->userRepository->create($user);

        $this->registerMail->send($user);
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
