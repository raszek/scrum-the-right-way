<?php

namespace App\Action\Profile;

use App\Entity\User\User;
use App\Enum\User\UserCodeTypeEnum;
use App\Exception\Profile\CannotChangeEmailException;
use App\Repository\User\UserCodeRepository;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class ConfirmChangeEmail
{

    public function __construct(
        private UserCodeRepository $userCodeRepository,
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function execute(string $activationCode, User $user): void
    {
        $userCode = $this->userCodeRepository->findLatestCode(
            activationCode: $activationCode,
            type: UserCodeTypeEnum::ChangeEmail,
            user: $user
        );

        if ($userCode === null) {
            throw new CannotChangeEmailException('Invalid activation code');
        }

        if ($this->clock->now()->greaterThan($userCode->getCreatedAt()->addHour())) {
            throw new CannotChangeEmailException('Cannot change email. Change email code is expired.');
        }

        $user->setEmail($userCode->getData()['email']);

        $this->entityManager->flush();
    }
}
