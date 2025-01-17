<?php

namespace App\Event\Thread\Renderer;

use App\Entity\Event\Event;
use App\Entity\Thread\Thread;
use App\Event\EventRecord;
use App\Event\Thread\Event\ThreadEvent;
use App\Helper\ArrayHelper;
use App\Repository\Thread\ThreadRepository;

readonly class ThreadEventRenderer
{

    public function __construct(
        private ThreadRepository $threadRepository
    ) {
    }

    /**
     * @param Event<ThreadEvent>[] $events
     * @param callable $render
     * @return array
     */
    public function fetch(array $events, callable $render): array
    {
        $mappedThreads = $this->getMappedThreads($events);

        $eventRecords = [];
        foreach ($events as $event) {
            $content = $render($event, $mappedThreads[$event->getData()->threadId]);

            $eventRecords[] = new EventRecord(
                id: $event->getId(),
                content: $content,
                createdAt: $event->getCreatedAt()
            );
        }

        return $eventRecords;
    }

    /**
     * @param Event<ThreadEvent>[] $events
     * @return array
     */
    private function getMappedThreads(array $events): array
    {
        $threadIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->threadId);

        $threads = $this->threadRepository->findInIds($threadIds);

        return ArrayHelper::indexByCallback($threads, fn(Thread $thread) => $thread->getId()->integerId());
    }
}
