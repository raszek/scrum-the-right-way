<?php

namespace App\Service\Thread;

use App\Entity\Thread\Thread;
use App\Entity\User\User;
use App\Repository\Thread\ThreadMessageRepository;
use App\Repository\Thread\ThreadStatusRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class ThreadEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private ThreadStatusRepository $threadStatusRepository,
        private EventPersisterFactory $eventPersisterFactory,
        private ThreadMessageRepository $threadMessageRepository,
    ) {
    }

    public function create(Thread $thread, User $user): ThreadEditor
    {
        return new ThreadEditor(
            thread: $thread,
            user: $user,
            entityManager: $this->entityManager,
            clock: $this->clock,
            threadStatusRepository: $this->threadStatusRepository,
            eventPersister: $this->eventPersisterFactory->create($thread->getProject(), $user),
            threadMessageRepository: $this->threadMessageRepository
        );
    }
}
