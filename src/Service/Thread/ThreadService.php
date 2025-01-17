<?php

namespace App\Service\Thread;

use App\Entity\Thread\Thread;
use App\Entity\Thread\ThreadMessage;
use App\Entity\Thread\ThreadRecord;
use App\Event\Thread\Event\CreateThreadEvent;
use App\Form\Thread\ThreadForm;
use App\Helper\ArrayHelper;
use App\Repository\Thread\ThreadMessageRepository;
use App\Repository\Thread\ThreadStatusRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class ThreadService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ThreadStatusRepository $threadStatusRepository,
        private ThreadMessageRepository $threadMessageRepository,
        private SluggerInterface $slugger,
        private ClockInterface $clock,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    /**
     * @param Thread[] $threads
     * @return ThreadRecord[]
     */
    public function getThreadRecords(array $threads): array
    {
        $messageCounts = $this->threadMessageRepository->threadMessageCounts($threads);

        $mappedMessageCounts = ArrayHelper::indexByKey($messageCounts, 'thread_id');

        $result = [];
        foreach ($threads as $thread) {
            $result[] = new ThreadRecord(
                id: $thread->getId(),
                title: $thread->getTitle(),
                slug: $thread->getSlug(),
                fullName: $thread->getCreatedBy()->getFullName(),
                status: $thread->getStatus()->getLabel(),
                postCount: $mappedMessageCounts[$thread->getId()->integerId()]['message_count'],
                updatedAt: $thread->getUpdatedAt()->format('Y-m-d H:i:s'),
            );
        }

        return $result;
    }

    public function createThread(ThreadForm $threadForm): void
    {
        $thread = new Thread(
            title: $threadForm->title,
            slug: $this->slugger->slug(mb_strtolower($threadForm->title)),
            createdBy: $threadForm->createdBy,
            project: $threadForm->project,
            status: $this->threadStatusRepository->openStatus(),
            createdAt: $this->clock->now()
        );

        $this->entityManager->persist($thread);

        $threadMessage = new ThreadMessage(
            content: $threadForm->message,
            number: 1,
            thread: $thread,
            createdBy: $threadForm->createdBy,
            createdAt: $this->clock->now()
        );

        $this->entityManager->persist($threadMessage);

        $this->entityManager->flush();

        $eventPersister = $this->eventPersisterFactory->create($threadForm->project, $threadForm->createdBy);
        $eventPersister->create(new CreateThreadEvent($thread->getId()->integerId()));

        $this->entityManager->flush();
    }

}
