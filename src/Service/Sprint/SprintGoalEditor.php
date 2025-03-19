<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\SprintGoal;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintGoalEditor
{

    public function __construct(
        private SprintGoal $sprintGoal,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function editName(string $newName): void
    {
        $this->sprintGoal->setName($newName);

        $this->entityManager->flush();
    }

}
