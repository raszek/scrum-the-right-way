<?php

namespace App\Service\Assignee;

use App\Entity\Issue\Issue;
use App\Entity\Project\ProjectMember;
use App\Entity\User\User;
use App\Event\Issue\Event\SetIssueAssigneeEvent;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use App\Service\Observer\IssueObserverEditor;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueAssigneeEditor
{

    public function __construct(
        private Issue $issue,
        User $user,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private EventPersister $eventPersister,
        private IssueObserverEditor $issueObserverEditor
    ) {
    }

    public function setAssignee(?ProjectMember $projectMember): void
    {
        $this->issue->setUpdatedAt($this->clock->now());
        $this->issue->setAssignee($projectMember);

        $this->entityManager->flush();

        if ($projectMember) {
            $this->issueObserverEditor->addObserverIfNotExists($projectMember);
        }

        $this->eventPersister->createIssueEvent(new SetIssueAssigneeEvent(
            issueId: $this->issue->getId()->integerId(),
            userId: $projectMember?->getUser()->getId()
        ), $this->issue);
    }
}
