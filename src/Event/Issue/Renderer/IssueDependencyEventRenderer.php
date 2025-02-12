<?php

namespace App\Event\Issue\Renderer;

use App\Entity\Event\Event;
use App\Event\EventRecord;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;

readonly class IssueDependencyEventRenderer
{

    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    public function fetchRecords(array $events, callable $render): array
    {
        $issueIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->issueId);
        $mappedIssues = $this->issueRepository->mappedIssues($issueIds);

        $dependencyIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->dependencyId);
        $mappedDependencies = $this->issueRepository->mappedIssues($dependencyIds);

        return ArrayHelper::map($events, function (Event $event) use (
            $mappedIssues,
            $mappedDependencies,
            $render
        ) {

            return new EventRecord(
                id: $event->getId(),
                content: $render(
                    $event,
                    $mappedIssues[$event->getData()->issueId],
                    $mappedDependencies[$event->getData()->dependencyId]
                ),
                createdAt: $event->getCreatedAt()
            );
        });
    }
}
