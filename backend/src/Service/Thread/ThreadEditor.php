<?php

namespace App\Service\Thread;

use App\Entity\Thread\Thread;
use App\Entity\Thread\ThreadMessage;
use App\Entity\User\User;
use App\Event\Thread\Event\AddThreadMessageEvent;
use App\Event\Thread\Event\CloseThreadEvent;
use App\Event\Thread\Event\OpenThreadEvent;
use App\Exception\Thread\ThreadAlreadyClosedException;
use App\Exception\Thread\ThreadAlreadyOpenedException;
use App\Form\Thread\MessageForm;
use App\Repository\Thread\ThreadMessageRepository;
use App\Repository\Thread\ThreadRepository;
use App\Repository\Thread\ThreadStatusRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class ThreadEditor
{
    public function __construct(
        private Thread $thread,
        private User $user,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private ThreadStatusRepository $threadStatusRepository,
        private EventPersister $eventPersister,
        private ThreadMessageRepository $threadMessageRepository,
    ) {
    }

    public function addMessage(MessageForm $form): void
    {
        $nextNumber = $this->threadMessageRepository->getMessageNextNumber($this->thread);

        $threadMessage = new ThreadMessage(
            content: $form->content,
            number: $nextNumber,
            thread: $this->thread,
            createdBy: $this->user,
            createdAt: $this->clock->now()
        );

        $this->entityManager->persist($threadMessage);

        $this->thread->setUpdatedAt($this->clock->now());

        $this->eventPersister->create(new AddThreadMessageEvent($this->thread->getId()->integerId()));
    }

    public function close(): void
    {
        if ($this->thread->isClosed()) {
            throw new ThreadAlreadyClosedException('Closed thread cannot be closed again.');
        }

        $this->thread->setStatus($this->threadStatusRepository->closedStatus());

        $this->thread->setUpdatedAt($this->clock->now());

        $this->eventPersister->create(new CloseThreadEvent($this->thread->getId()->integerId()));
    }

    public function open(): void
    {
        if ($this->thread->isOpen()) {
            throw new ThreadAlreadyOpenedException('Open thread cannot be reopened again.');
        }

        $this->thread->setStatus($this->threadStatusRepository->openStatus());

        $this->thread->setUpdatedAt($this->clock->now());

        $this->eventPersister->create(new OpenThreadEvent($this->thread->getId()->integerId()));
    }
}
