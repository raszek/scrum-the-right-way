<?php

namespace App\Service\Issue\IssueEditor;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersisterFactory;
use App\Service\Sprint\SprintIssueEditorStrategy;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueEditorFactory
{

    public function __construct(
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private EventPersisterFactory $eventPersisterFactory,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
    ) {
    }

    public function create(Issue $issue, User $user): IssueEditor
    {
        return new IssueEditor(
            issue: $issue,
            issueRepository: $this->issueRepository,
            issueColumnRepository: $this->issueColumnRepository,
            projectIssueEditorStrategy: $this->getProjectIssueEditorStrategy($issue),
            entityManager: $this->entityManager,
            clock: $this->clock,
            eventPersister: $this->eventPersisterFactory->create($issue->getProject(), $user)
        );
    }

    private function getProjectIssueEditorStrategy(Issue $issue): ProjectIssueEditorStrategy
    {
        return new SprintIssueEditorStrategy(
            issue: $issue,
            sprintGoalIssueRepository: $this->sprintGoalIssueRepository,
            clock: $this->clock,
        );
    }

}
