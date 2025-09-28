<?php

namespace App\Action\Site;

use App\Email\Site\ResetPasswordMessage;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Exception\Site\UserNotFoundException;
use App\Form\Site\ForgotPasswordFormData;
use App\Repository\User\UserCodeRepository;
use App\Repository\User\UserRepository;
use App\Service\Common\ClockInterface;
use App\Service\Common\RandomService;

readonly class ResetPassword
{

    public function __construct(
        private UserRepository $userRepository,
        private UserCodeRepository $userCodeRepository,
        private RandomService $randomService,
        private ClockInterface $clock,
        private ResetPasswordMessage $resetPasswordEmail,
    ) {
    }

    public function execute(ForgotPasswordFormData $formData): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $formData->email
        ]);

        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        $userCode = new UserCode(
            mainUser: $user,
            type: UserCodeTypeEnum::ResetPassword,
            code: $this->randomService->randomString(),
            createdAt: $this->clock->now()
        );

        $this->userCodeRepository->create($userCode);

        $this->resetPasswordEmail->send($userCode);
    }

}
