<?php

namespace App\Action\Profile;

use App\Entity\User\User;
use App\Form\Profile\ChangePasswordFormData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ChangePassword
{

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager
    ) {
    }


    public function execute(ChangePasswordFormData $formData, User $user): void
    {
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $formData->newPassword);

        $user->setPasswordHash($hashedPassword);

        $this->entityManager->flush();
    }
}
