<?php

namespace App\Controller\Room;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Entity\Room\Room;
use App\Repository\Room\RoomRepository;
use App\Security\Voter\RoomVoter;
use App\Service\Websocket\WebsocketService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class RoomController extends Controller
{

    public function __construct(
        private readonly WebsocketService $websocketService,
        private readonly RoomRepository $roomRepository,
    ) {
    }

    #[Route('/rooms/{roomId}', 'app_project_room_view')]
    public function view(Project $project, string $roomId): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::VIEW_ROOM, $project);

        $room = $this->findRoom($roomId);

        return $this->render('room/view.html.twig', [
            'project' => $project,
            'websocketUrl' => $this->websocketService->getUrlConnection(
                path: '/rooms/'.$roomId,
                user: $this->getLoggedInUser(),
            ),
            'roomIssues' => $room->getRoomIssues()
        ]);
    }

    private function findRoom(string $roomId): Room
    {
        $room = $this->roomRepository->findOneBy([
            'id' => $roomId
        ]);

        if (!$room) {
            throw new NotFoundHttpException('Room not found');
        }

        return $room;
    }
}
