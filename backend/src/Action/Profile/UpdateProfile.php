<?php

namespace App\Action\Profile;

use App\Entity\User\User;
use App\Form\Profile\ProfileFormData;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateProfile
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(ProfileFormData $profileFormData, User $user): void
    {
        $user->setFirstName($profileFormData->firstName);
        $user->setLastName($profileFormData->lastName);

        $this->entityManager->flush();
    }
}
