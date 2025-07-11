<?php

namespace App\Controller\Room;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Entity\Room\Room;
use App\Repository\Issue\IssueRepository;
use App\Repository\Room\RoomRepository;
use App\Security\Voter\RoomVoter;
use App\Service\Issue\StoryPointService;
use App\Service\Room\ProjectRoomEditorFactory;
use App\Service\Room\UserRoomSettings;
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
        private readonly IssueRepository $issueRepository,
        private readonly StoryPointService $storyPointService,
        private readonly UserRoomSettings $userRoomSettings
    ) {
    }

    #[Route('/rooms/{roomId}', 'app_project_room_view')]
    public function view(Project $project, string $roomId): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::VIEW_ROOM, $project);

        $room = $this->findRoom($roomId);

        return $this->render('room/view.html.twig', [
            'project' => $project,
            'room' => $room,
            'roomIssues' => $room->getRoomIssues()->map(fn($issue) => $issue->getIssue()),
            'recommendedStoryPoints' => $this->storyPointService->recommendedStoryPoints(),
            'websocketUrl' => $this->websocketService->getUrlConnection(
                path: sprintf('projects/%s/rooms/%s', $project->getId(), $room->getId()),
                user: $this->getLoggedInUser(),
            ),
            'tab' => $this->userRoomSettings->getTab()->value,
        ]);
    }

    #[Route('/rooms', 'app_project_room_create', methods: ['POST'])]
    public function create(Project $project, Request $request, ProjectRoomEditorFactory $factory): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::VIEW_ROOM, $project);

        $projectRoomEditor = $factory->create($project);

        $issues = $this->issueRepository->findByIds($request->get('issueIds'), $project);

        $createdRoom = $projectRoomEditor->create($issues);

        return $this->redirectToRoute('app_project_room_view', [
            'id' => $project->getId(),
            'roomId' => $createdRoom->getId()
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
