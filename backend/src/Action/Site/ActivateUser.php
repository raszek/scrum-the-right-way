<?php

namespace App\Action\Site;

use App\Exception\Site\CannotActivateUserException;
use App\Form\Site\ResetPasswordForm;
use App\Repository\User\UserRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ActivateUser
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private ClockInterface $clock
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
            throw new CannotActivateUserException('User not found');
        }

        if ($user->isActive()) {
            throw new CannotActivateUserException('User is already active');
        }

        $activationSendDate = CarbonImmutable::instance($user->getActivationCodeSendDate());
        if ($this->clock->now()->greaterThan($activationSendDate->addHour())) {
            throw new CannotActivateUserException('Activation link is only valid for 1 hour. Ask admin for another activation link.');
        }

        $user->setPasswordHash($this->userPasswordHasher->hashPassword($user, $form->password));
        $user->setActivationCode(null);

        $this->userRepository->flush();
    }
}
