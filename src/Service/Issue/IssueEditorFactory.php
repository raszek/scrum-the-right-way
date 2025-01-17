<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Repository\Issue\IssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueEditorFactory
{

    public function __construct(
        private IssueRepository $issueRepository,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    public function create(Issue $issue, User $user): IssueEditor
    {
        return new IssueEditor(
            issue: $issue,
            issueRepository: $this->issueRepository,
            entityManager: $this->entityManager,
            clock: $this->clock,
            eventPersister: $this->eventPersisterFactory->create($issue->getProject(), $user)
        );
    }

}
