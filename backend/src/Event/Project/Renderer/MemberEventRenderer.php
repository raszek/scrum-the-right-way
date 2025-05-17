<?php

namespace App\Event\Project\Renderer;

use App\Entity\Event\Event;
use App\Event\EventRecord;
use App\Event\Project\Event\RoleEvent;
use App\Helper\ArrayHelper;
use App\Repository\User\UserRepository;

readonly class MemberEventRenderer
{

    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @param Event<RoleEvent>[] $events
     * @return EventRecord[]
     */
    public function fetch(array $events, callable $render): array
    {
        $mappedRoles = $this->getMappedUsers($events);

        $eventRecords = [];
        foreach ($events as $event) {
            $content = $render(
                $event,
                $mappedRoles[$event->getData()->userId],
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
     * @param Event<RoleEvent>[] $events
     * @return array
     */
    private function getMappedUsers(array $events): array
    {
        $userIds = ArrayHelper::map($events, fn(Event $event) => $event->getData()->userId);

        return $this->userRepository->mappedUsers($userIds);
    }
}
