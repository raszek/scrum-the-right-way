<?php

namespace App\Action\Site;

use App\Enum\User\UserCodeTypeEnum;
use App\Exception\Site\CannotResetPasswordException;
use App\Exception\User\CannotUseCodeException;
use App\Form\Site\ResetPasswordFormData;
use App\Repository\User\UserRepository;
use App\Service\User\UserCodeService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ConfirmResetPassword
{

    public function __construct(
        private UserCodeService $userCodeService,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    /**
     * @param ResetPasswordFormData $resetPasswordForm
     * @return void
     * @throws CannotResetPasswordException
     */
    public function execute(ResetPasswordFormData $resetPasswordForm): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $resetPasswordForm->email,
        ]);

        try {
            $this->userCodeService->useCode(
                code: $resetPasswordForm->resetPasswordCode,
                type: UserCodeTypeEnum::ResetPassword,
                user: $user
            );
        } catch (CannotUseCodeException $e) {
            throw new CannotResetPasswordException($e->getMessage());
        }

        $passwordHash = $this->userPasswordHasher->hashPassword($user, $resetPasswordForm->password);

        $user->setPasswordHash($passwordHash);

        $this->userRepository->flush();
    }

}
