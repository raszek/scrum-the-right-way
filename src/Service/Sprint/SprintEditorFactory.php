<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\Sprint;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private IssueColumnRepository $issueColumnRepository,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
        private SprintGoalRepository $sprintGoalRepository,
    ) {
    }

    public function create(Sprint $sprint): SprintEditor
    {
        return new SprintEditor(
            sprint: $sprint,
            entityManager: $this->entityManager,
            issueColumnRepository: $this->issueColumnRepository,
            sprintGoalIssueRepository: $this->sprintGoalIssueRepository,
            sprintGoalRepository: $this->sprintGoalRepository
        );
    }
}
