<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Event\EventRecord;
use App\Event\Issue\Event\IssueEvent;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;

readonly class IssueEventRenderer
{

    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    /**
     * @param Event<IssueEvent>[] $events
     * @return EventRecord[]
     */
    public function fetch(array $events, callable $render): array
    {
        $mappedRoles = $this->getMappedIssues($events);

        $eventRecords = [];
        foreach ($events as $event) {
            $content = $render(
                $event,
                $mappedRoles[$event->getData()->issueId],
            );

            $eventRecords[] = new EventRecord(
                id: $event->getId(),
                content: $content,
                createdAt: $event->getCreatedAt()
            );
        }

        return $eventRecords;
    }


    /**
     * @param Event<IssueEvent>[] $events
     * @return array
     */
    private function getMappedIssues(array $events): array
    {
        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);

        return $this->issueRepository->mappedIssues($issueIds);
    }
}
