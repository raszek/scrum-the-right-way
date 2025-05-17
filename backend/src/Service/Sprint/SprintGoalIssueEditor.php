<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Position\Positioner;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

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
        if ($this->sprintGoalIssue->getIssue()->isSubIssue()) {
            throw new RuntimeException('Sub issues cannot be moved on sprint view');
        }

        $this->sprintGoalIssue->setSprintGoal($sprintGoal);
        $subIssues = $this->findSubIssues();
        foreach ($subIssues as $subIssue) {
            $subIssue->setSprintGoal($sprintGoal);
        }

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

    private function findSubIssues(): array
    {
        if (!$this->sprintGoalIssue->getIssue()->isFeature()) {
            return [];
        }

        return $this->sprintGoalIssueRepository->findFeatureSubIssues($this->sprintGoalIssue);
    }

}
