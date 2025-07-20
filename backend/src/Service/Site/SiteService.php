<?php

namespace App\Service\Site;

use App\Entity\User\User;
use App\Exception\Site\CannotResetPasswordException;
use App\Exception\Site\UserNotFoundException;
use App\Form\Site\ResetPasswordForm;
use App\Repository\User\UserRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;

readonly class SiteService
{

    public function __construct(
        private UserRepository $userRepository,
        private ForgotPasswordMail $forgotPasswordMail,
        private UserPasswordHasherInterface $userPasswordHasher,
        private ClockInterface $clock
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
            throw new CannotResetPasswordException('User not found');
        }

        $this->guardAgainstResetPasswordCodeExpire($user);

        $passwordHash = $this->userPasswordHasher->hashPassword($user, $resetPasswordForm->password);

        $user->setPasswordHash($passwordHash);
        $user->setResetPasswordCode(null);

        $this->userRepository->flush();
    }

    private function guardAgainstResetPasswordCodeExpire(User $user): void
    {
        $exception = new CannotResetPasswordException('Reset password link is only valid for 1 hour. Reset password again.');

        if ($user->getResetPasswordCodeSendDate() === null) {
            throw $exception;
        }

        $resetPasswordActivationDate = CarbonImmutable::instance($user->getResetPasswordCodeSendDate());
        if ($this->clock->now()->greaterThan($resetPasswordActivationDate->addHour())) {
            throw $exception;
        }
    }
}
