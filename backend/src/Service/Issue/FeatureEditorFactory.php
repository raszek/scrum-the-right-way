<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Issue\IssueTypeRepository;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintRepository;
use App\Service\Common\ClockInterface;
use App\Service\Sprint\SprintIssueEditorStrategy;
use Doctrine\ORM\EntityManagerInterface;

readonly class FeatureEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
        private IssueTypeRepository $issueTypeRepository,
        private ClockInterface $clock,
        private SprintRepository $sprintRepository,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
    ) {
    }

    public function create(Issue $issue, User $user): FeatureEditor
    {
        return new FeatureEditor(
            issue: $issue,
            user: $user,
            entityManager: $this->entityManager,
            issueRepository:  $this->issueRepository,
            issueColumnRepository: $this->issueColumnRepository,
            issueTypeRepository: $this->issueTypeRepository,
            clock: $this->clock,
            projectIssueEditorStrategy: $this->getProjectIssueEditorStrategy($issue)
        );
    }

    private function getProjectIssueEditorStrategy(Issue $issue): SprintIssueEditorStrategy
    {
        return new SprintIssueEditorStrategy(
            issue: $issue,
            sprintGoalIssueRepository: $this->sprintGoalIssueRepository,
            sprintRepository: $this->sprintRepository,
            clock: $this->clock,
        );
    }

}
