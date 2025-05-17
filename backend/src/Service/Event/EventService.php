<?php

namespace App\Service\Event;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Event\EventRecord;
use App\Event\EventRendererInterface;
use App\Repository\Event\EventRepository;
use Exception;

readonly class EventService
{

    public function __construct(
        private EventRendererFactory $eventRendererFactory,
        private EventRepository $eventRepository
    ) {
    }

    /**
     * @param Issue $issue
     * @return EventRecord[]
     * @throws Exception
     */
    public function getIssueEventRecords(Issue $issue): array
    {
        $events = $this->eventRepository->issueEvents($issue);

        return $this->fetchIssueEventRecords($events);
    }


    /**
     * @param Event[] $events
     * @return EventRecord[]
     * @throws Exception
     */
    public function getEventRecords(array $events): array
    {
        $groupedEvents = $this->groupEventParamsByName($events);

        $eventRecords = [];
        foreach ($groupedEvents as $eventName => $events) {
            $renderer = $this->eventRendererFactory->getEventRenderer($eventName);

            $this->setEventsData($events, $renderer);

            $eventRecords = array_merge($eventRecords, $renderer->fetch($events));
        }

        usort(
            $eventRecords,
            fn(EventRecord $r1, EventRecord $r2) => $r2->createdAt->getTimestamp() - $r1->createdAt->getTimestamp()
        );

        return $eventRecords;
    }

    /**
     * @throws Exception
     */
    private function fetchIssueEventRecords(array $events): array
    {
        $groupedEvents = $this->groupEventParamsByName($events);

        $eventRecords = [];
        foreach ($groupedEvents as $eventName => $events) {
            $renderer = $this->eventRendererFactory->getIssueEventRenderer($eventName);

            $this->setEventsData($events, $renderer);

            $eventRecords = array_merge($eventRecords, $renderer->fetchForIssue($events));
        }

        usort(
            $eventRecords,
            fn(EventRecord $r1, EventRecord $r2) => $r2->createdAt->getTimestamp() - $r1->createdAt->getTimestamp()
        );

        return $eventRecords;
    }

    /**
     * @param Event[] $events
     * @return array
     */
    private function groupEventParamsByName(array $events): array
    {
        $groupedEvents = [];
        
        foreach ($events as $event) {
            $groupedEvents[$event->getName()][] = $event;
        }
        
        return $groupedEvents;
    }

    /**
     * @param Event[] $events
     * @param EventRendererInterface $renderer
     * @return void
     */
    private function setEventsData(array $events, EventRendererInterface $renderer): void
    {
        $eventDataClass = $renderer->eventDataClass();

        foreach ($events as $event) {
            $event->setData($eventDataClass::fromArray($event->getParams()));
        }
    }
}
