<?php

namespace App\Service\Notification;

use App\Entity\User\User;
use App\Repository\User\UserRepository;
use App\Service\Event\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class SendNotificationEmails
{

    public function __construct(
        private UserRepository $userRepository,
        private EventService $eventService,
        private EntityManagerInterface $entityManager,
        private NotificationMail $notificationMail
    ) {
    }

    public function execute(): void
    {
        $query = $this->userRepository->userNotificationsQuery()->getQuery();

        foreach($query->toIterable() as $user) {
            $this->sendToUser($user);
        }
    }

    /**
     * @param User $destinationUser
     * @return void
     * @throws Exception
     */
    private function sendToUser(User $destinationUser): void
    {
        $events = [];
        foreach ($destinationUser->getNotificationsToSend() as $notification) {
            $notification->setSentEmail(true);
            $events[] = $notification->getEvent();
        }

        if (!$events) {
            return;
        }

        $eventRecords = $this->eventService->getEventRecords($events);

        $this->notificationMail->send($eventRecords, $destinationUser);

        $this->entityManager->flush();
    }
}
