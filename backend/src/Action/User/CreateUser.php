<?php

namespace App\Action\User;

use App\Entity\Profile\Profile;
use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Form\User\UserFormData;
use App\Message\Site\ActivationUserMessage;
use App\Service\Common\ClockInterface;
use App\Service\Common\RandomService;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateUser
{

    public function __construct(
        private ClockInterface $clock,
        private ActivationUserMessage $registerMail,
        private EntityManagerInterface $entityManager,
        private RandomService $randomService
    ) {
    }

    public function execute(UserFormData $form): User
    {
        $profile = new Profile();

        $user = new User(
            email: $form->email,
            firstName: $form->firstName,
            lastName: $form->lastName,
            profile: $profile,
            createdAt: $this->clock->now(),
        );

        $userCode = new UserCode(
            mainUser: $user,
            type: UserCodeTypeEnum::Activation,
            code: $this->randomService->randomString(),
            createdAt: $this->clock->now()
        );

        $this->entityManager->persist($profile);
        $this->entityManager->persist($user);
        $this->entityManager->persist($userCode);

        $this->entityManager->flush();

        $this->registerMail->send($userCode);

        return $user;
    }

}
