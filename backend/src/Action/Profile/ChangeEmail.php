<?php

namespace App\Action\Profile;

use App\Email\Profile\ChangeEmailEmail;
use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Form\Profile\ChangeEmailFormData;
use App\Service\Common\ClockInterface;
use App\Service\Common\RandomService;
use Doctrine\ORM\EntityManagerInterface;

readonly class ChangeEmail
{

    public function __construct(
        private ChangeEmailEmail $changeEmailEmail,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private RandomService $randomService,
    ) {
    }

    public function execute(ChangeEmailFormData $formData, User $user): void
    {
        $userCode = new UserCode(
            user: $user,
            type: UserCodeTypeEnum::ChangeEmail->value,
            code: $this->randomService->randomString(),
            createdAt: $this->clock->now(),
            data: [
                'email' => $formData->email,
            ]
        );

        $this->entityManager->persist($userCode);

        $this->entityManager->flush();

        $this->changeEmailEmail->send($userCode);
    }

}
