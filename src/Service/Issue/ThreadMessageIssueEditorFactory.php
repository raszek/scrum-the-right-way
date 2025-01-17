<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class ThreadMessageIssueEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    public function create(Issue $issue, User $user): ThreadMessageIssueEditor
    {
        return new ThreadMessageIssueEditor(
            issue: $issue,
            entityManager: $this->entityManager,
            eventPersister: $this->eventPersisterFactory->create($issue->getProject(), $user)
        );
    }
}
