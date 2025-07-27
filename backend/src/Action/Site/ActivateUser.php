<?php

namespace App\Action\Site;

use App\Enum\User\UserCodeTypeEnum;
use App\Enum\User\UserStatusEnum;
use App\Exception\Site\CannotActivateUserException;
use App\Exception\User\CannotUseCodeException;
use App\Form\Site\ResetPasswordFormData;
use App\Repository\User\UserRepository;
use App\Service\User\UserCodeService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ActivateUser
{

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher,
        private UserCodeService $userCodeService,
    ) {
    }

    public function execute(ResetPasswordFormData $form): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $form->email,
        ]);

        try {
            $this->userCodeService->useCode(
                code: $form->resetPasswordCode,
                type: UserCodeTypeEnum::Activation,
                user: $user
            );
        } catch (CannotUseCodeException $e) {
            throw new CannotActivateUserException($e->getMessage());
        }

        $user->setPasswordHash($this->userPasswordHasher->hashPassword($user, $form->password));
        $user->setStatusId(UserStatusEnum::Active);

        $this->userRepository->flush();
    }
}
