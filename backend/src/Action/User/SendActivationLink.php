<?php

namespace App\Action\User;

use App\Message\Site\ActivationUserMessage;
use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Service\Common\ClockInterface;
use App\Service\Common\RandomService;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

readonly class SendActivationLink
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RandomService $randomService,
        private ClockInterface $clock,
        private ActivationUserMessage $activationMail,
    ) {
    }

    public function execute(User $user): void
    {
        if ($user->isActive()) {
            throw new DomainException('Cannot send activation link when user is active.');
        }

        $userCode = new UserCode(
            mainUser: $user,
            type: UserCodeTypeEnum::Activation,
            code: $this->randomService->randomString(),
            createdAt: $this->clock->now(),
        );

        $this->entityManager->persist($userCode);

        $this->entityManager->flush();

        $this->activationMail->send($userCode);
    }
}
