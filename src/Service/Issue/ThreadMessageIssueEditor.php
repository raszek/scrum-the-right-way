<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueThreadMessage;
use App\Entity\Thread\ThreadMessage;
use App\Event\Issue\Event\AddIssueThreadMessageEvent;
use App\Event\Issue\Event\RemoveIssueThreadMessageEvent;
use App\Exception\Issue\CannotAddIssueThreadMessageException;
use App\Exception\Issue\CannotRemoveIssueThreadMessageException;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class ThreadMessageIssueEditor
{
    public function __construct(
        private Issue $issue,
        private EntityManagerInterface $entityManager,
        private EventPersister $eventPersister,
        private ClockInterface $clock
    ) {
    }

    public function addMessage(ThreadMessage $threadMessage): void
    {
        $alreadyExistMessage = $this->issue->getIssueThreadMessages()
            ->findFirst(fn(int $i, IssueThreadMessage $message) => $message->getThreadMessage()->getId() === $threadMessage->getId());

        if ($alreadyExistMessage) {
            throw new CannotAddIssueThreadMessageException('Thread message already added to this issue.');
        }

        $issueMessage = new IssueThreadMessage(
            issue: $this->issue,
            threadMessage: $threadMessage
        );

        $this->entityManager->persist($issueMessage);

        $this->issue->setUpdatedAt($this->clock->now());

        $this->eventPersister->createIssueEvent(new AddIssueThreadMessageEvent(
            issueId: $this->issue->getId()->integerId(),
            threadMessageId: $threadMessage->getId()->integerId()
        ), $this->issue);

        $this->entityManager->flush();
    }

    public function removeMessage(ThreadMessage $threadMessage): void
    {
        $foundIssueMessage = $this->issue->getIssueThreadMessages()->findFirst(
            fn(int $i, IssueThreadMessage $issueMessage) => $issueMessage->getThreadMessage()->getId() === $threadMessage->getId()
        );

        if (!$foundIssueMessage) {
            throw new CannotRemoveIssueThreadMessageException('Issue message not found');
        }

        $this->issue->removeMessage($foundIssueMessage);

        $this->entityManager->remove($foundIssueMessage);

        $this->issue->setUpdatedAt($this->clock->now());

        $this->eventPersister->createIssueEvent(new RemoveIssueThreadMessageEvent(
            issueId: $this->issue->getId()->integerId(),
            threadMessageId: $threadMessage->getId()->integerId()
        ), $this->issue);

        $this->entityManager->flush();
    }

}
