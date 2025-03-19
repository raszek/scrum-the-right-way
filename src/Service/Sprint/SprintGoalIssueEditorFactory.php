<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\SprintGoalIssue;
use App\Repository\Sprint\SprintGoalIssueRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintGoalIssueEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
    ) {
    }

    public function create(SprintGoalIssue $sprintGoalIssue): SprintGoalIssueEditor
    {
        return new SprintGoalIssueEditor(
            sprintGoalIssue: $sprintGoalIssue,
            entityManager: $this->entityManager,
            sprintGoalIssueRepository: $this->sprintGoalIssueRepository,
        );
    }

}
