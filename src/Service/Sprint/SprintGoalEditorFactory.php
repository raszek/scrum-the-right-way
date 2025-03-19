<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\SprintGoal;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintGoalEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(SprintGoal $sprintGoal): SprintGoalEditor
    {
        return new SprintGoalEditor(
            sprintGoal: $sprintGoal,
            entityManager: $this->entityManager,
        );
    }

}
