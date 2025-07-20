<?php

namespace App\Action\User;

use App\Entity\User\User;
use App\Service\Common\ClockInterface;
use App\Service\Common\RandomService;
use App\Service\Site\ActivationUserEmail;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

readonly class SendActivationLink
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RandomService $randomService,
        private ClockInterface $clock,
        private ActivationUserEmail $activationMail,
    ) {
    }

    public function execute(User $user): void
    {
        if ($user->isActive()) {
            throw new DomainException('Cannot send activation link when user is active.');
        }

        $user->setActivationCode($this->randomService->randomString());
        $user->setActivationCodeSendDate($this->clock->now());

        $this->entityManager->flush();

        $this->activationMail->send($user);
    }
}
