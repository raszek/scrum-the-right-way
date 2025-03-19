<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\SprintGoal;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Repository\Sprint\SprintGoalRepository;
use App\Service\Position\Positioner;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintGoalEditor
{

    public function __construct(
        private SprintGoal $sprintGoal,
        private EntityManagerInterface $entityManager,
        private SprintGoalRepository $sprintGoalRepository,
    ) {
    }

    public function editName(string $newName): void
    {
        $this->sprintGoal->setName($newName);

        $this->entityManager->flush();
    }

    /**
     * @param int $position
     * @return void
     * @throws OutOfBoundPositionException
     */
    public function setPosition(int $position): void
    {
        $query = $this->sprintGoalRepository->sprintGoalQuery($this->sprintGoal->getSprint());
        $query->andWhere('sprintGoal.id <> :id');
        $query->sqidParameter('id', $this->sprintGoal->getId());

        $positioner = new Positioner(
            query: $query,
            positioned: $this->sprintGoal,
            reorderService: $this->sprintGoalRepository
        );

        $positioner->setPosition($position);

        $this->entityManager->flush();
    }

}
