<?php

namespace App\Service\Event;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Entity\User\UserNotification;
use App\Event\EventInterface;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class EventPersister
{

    public function __construct(
        private Project $project,
        private User $user,
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createIssueEvent(EventInterface $event, Issue $issue): Event
    {
        $createdEvent = new Event(
            name: $event->name(),
            params: $event->toArray(),
            project: $this->project,
            createdAt: $this->clock->now(),
            createdBy: $this->user,
            issue: $issue
        );

        $this->entityManager->persist($createdEvent);

        $this->createNotifications($createdEvent, $issue);

        $this->entityManager->flush();

        return $createdEvent;
    }

    public function create(EventInterface $event): Event
    {
        $createdEvent = new Event(
            name: $event->name(),
            params: $event->toArray(),
            project: $this->project,
            createdAt: $this->clock->now(),
            createdBy: $this->user
        );

        $this->entityManager->persist($createdEvent);

        $this->entityManager->flush();

        return $createdEvent;
    }

    private function createNotifications(Event $event, Issue $issue): void
    {
        foreach ($issue->getObservers() as $observer) {
            $forUser = $observer->getProjectMember()->getUser();

            if ($forUser->getId() === $this->user->getId()) {
                continue;
            }

            $userNotification = new UserNotification(
                event: $event,
                forUser: $observer->getProjectMember()->getUser()
            );

            $this->entityManager->persist($userNotification);
        }
    }

}
