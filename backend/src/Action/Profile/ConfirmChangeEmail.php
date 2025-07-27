<?php

namespace App\Action\Profile;

use App\Entity\User\User;
use App\Enum\User\UserCodeTypeEnum;
use App\Exception\Profile\CannotChangeEmailException;
use App\Exception\User\CannotUseCodeException;
use App\Service\User\UserCodeService;
use Doctrine\ORM\EntityManagerInterface;

readonly class ConfirmChangeEmail
{

    public function __construct(
        private UserCodeService $userCodeService,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function execute(string $activationCode, User $user): void
    {
        try {
            $userCode = $this->userCodeService->useCode(
                code: $activationCode,
                type: UserCodeTypeEnum::ChangeEmail,
                user: $user
            );
        } catch (CannotUseCodeException $e) {
            throw new CannotChangeEmailException($e->getMessage());
        }

        $user->setEmail($userCode->getData()['email']);

        $this->entityManager->flush();
    }
}
