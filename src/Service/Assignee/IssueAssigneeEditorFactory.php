<?php

namespace App\Service\Assignee;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersisterFactory;
use App\Service\Observer\IssueObserverEditorFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueAssigneeEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory,
        private IssueObserverEditorFactory $issueObserverEditorFactory,
        private ClockInterface $clock
    ) {
    }

    public function create(Issue $issue, User $user): IssueAssigneeEditor
    {
        return new IssueAssigneeEditor(
            issue: $issue,
            user: $user,
            entityManager: $this->entityManager,
            clock: $this->clock,
            eventPersister: $this->eventPersisterFactory->create($issue->getProject(), $user),
            issueObserverEditor: $this->issueObserverEditorFactory->create($issue)
        );
    }

}
