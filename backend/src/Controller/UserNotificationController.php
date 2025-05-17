<?php

namespace App\Controller;

use App\Entity\User\UserNotification;
use App\Event\EventRecord;
use App\Helper\ArrayHelper;
use App\Repository\User\UserNotificationRepository;
use App\Security\Voter\EditUserNotificationVoter;
use App\Service\Event\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserNotificationController extends Controller
{

    public function __construct(
        private readonly PaginatorInterface         $paginator,
        private readonly UserNotificationRepository $userNotificationRepository,
        private readonly EventService $eventService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/user/notifications', name: 'app_user_notification_list')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request): Response
    {
        $query = $this->userNotificationRepository->userNotificationQuery($this->getLoggedInUser());

        $pagination = $this->paginator->paginate(
            target: $query,
            page: $request->query->getInt('page', 1),
            limit: 10
        );

        return $this->render('user_notification/index.html.twig', [
            'items' => $this->getUserNotifications($pagination->getItems()),
            'pagination' => $pagination,
        ]);
    }

    #[Route('/user/notifications/latest', name: 'app_user_notification_latest')]
    #[IsGranted('ROLE_USER')]
    public function latest(): Response
    {
        $notifications = $this->userNotificationRepository->latestNotifications($this->getLoggedInUser());

        $events = ArrayHelper::map(
            $notifications,
            fn (UserNotification $userNotification) => $userNotification->getEvent()
        );

        $eventRecords = $this->eventService->getEventRecords($events);

        return $this->render('user_notification/latest.html.twig', [
            'eventRecords' => $eventRecords,
        ]);
    }

    #[Route('/user/notifications/mark-all-read', name: 'app_user_notification_mark_all_read', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAllRead(): Response
    {
        $this->userNotificationRepository->markAllNotificationsRead($this->getLoggedInUser());

        return new Response(status: 204);
    }

    #[Route('/user/notifications/{id}/mark-read', name: 'app_user_notification_mark_read', methods: ['POST'])]
    public function markRead(UserNotification $userNotification): Response
    {
        $this->denyAccessUnlessGranted(EditUserNotificationVoter::MARK_READ_NOTIFICATION, $userNotification);

        $userNotification->setRead(true);

        $this->entityManager->flush();

        return new Response(status: 204);
    }

    #[Route('/user/notifications/{id}/mark-unread', name: 'app_user_notification_mark_unread', methods: ['POST'])]
    public function markUnread(UserNotification $userNotification): Response
    {
        $this->denyAccessUnlessGranted(EditUserNotificationVoter::MARK_UNREAD_NOTIFICATION, $userNotification);

        $userNotification->setRead(false);

        $this->entityManager->flush();

        return new Response(status: 204);
    }

    /**
     * @param UserNotification[] $userNotifications
     * @return array
     * @throws Exception
     */
    private function getUserNotifications(array $userNotifications): array
    {
        $events = ArrayHelper::map(
            $userNotifications,
            fn (UserNotification $userNotification) => $userNotification->getEvent()
        );

        $indexedEventRecords = ArrayHelper::indexByCallback(
            $this->eventService->getEventRecords($events),
            fn(EventRecord $eventRecord) => $eventRecord->id
        );

        $result = [];
        foreach ($userNotifications as $userNotification) {
            $result[] = [
                'notification' => $userNotification,
                'eventRecord' => $indexedEventRecords[$userNotification->getEvent()->getId()]
            ];
        }

        return $result;
    }
}
