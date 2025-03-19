<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Position\Positioner;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintGoalIssueEditor
{

    public function __construct(
        private SprintGoalIssue $sprintGoalIssue,
        private EntityManagerInterface $entityManager,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
    ) {
    }

    public function move(SprintGoal $sprintGoal, int $position): void
    {
        $this->sprintGoalIssue->setSprintGoal($sprintGoal);

        $query = $this->sprintGoalIssueRepository->sprintGoalIssueQuery($sprintGoal);
        $query->andWhere('sprintGoalIssue.id <> :issueId');
        $query->sqidParameter('issueId', $this->sprintGoalIssue->getId());

        $positioner = new Positioner(
            query: $query,
            positioned: $this->sprintGoalIssue,
            reorderService: $this->sprintGoalIssueRepository
        );

        $positioner->setPosition($position);

        $this->entityManager->flush();
    }

}
