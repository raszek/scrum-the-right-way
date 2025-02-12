<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class DependencyIssueEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory,
    ) {
    }

    public function create(Issue $issue, User $user): DependencyIssueEditor
    {
        return new DependencyIssueEditor(
            issue: $issue,
            entityManager: $this->entityManager,
            eventPersister: $this->eventPersisterFactory->create(
                project: $issue->getProject(),
                user: $user
            )
        );
    }
}
