<?php

namespace App\Controller\Room;

use App\Controller\Controller;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Room\Room;
use App\Repository\Issue\IssueRepository;
use App\Repository\Room\RoomIssueRepository;
use App\Repository\Room\RoomRepository;
use App\Security\Voter\RoomVoter;
use App\Service\Issue\StoryPointService;
use App\Service\Room\RoomEditorFactory;
use App\Service\Room\RoomService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class RoomIssueController extends Controller
{

    public function __construct(
        private readonly RoomIssueRepository $roomIssueRepository,
        private readonly RoomRepository $roomRepository,
        private readonly IssueRepository $issueRepository,
        private readonly StoryPointService $storyPointService,
        private readonly RoomService $roomService,
        private readonly RoomEditorFactory $roomEditorFactory,
    ) {
    }

    #[Route('/rooms/{roomId}/issues/{issueId}', 'app_project_room_issue_view', methods: ['GET'])]
    public function view(Project $project, string $roomId, string $issueId): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::VIEW_ROOM_ISSUE, $project);

        $roomIssue = $this->roomIssueRepository->findRoomIssue($issueId, $roomId, $project);

        if (!$roomIssue) {
            throw new NotFoundHttpException('Room issue not found');
        }

        return $this->render('room/room_issue_view.html.twig', [
            'project' => $project,
            'issue' => $roomIssue->getIssue(),
            'recommendedStoryPoints' => $this->storyPointService->recommendedStoryPoints(),
        ]);
    }

    #[Route('/rooms/{roomId}/issues', 'app_project_room_search_issues', methods: ['GET'])]
    public function searchRoomIssues(Project $project, string $roomId, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RoomVoter::SEARCH_ROOM_ISSUES, $project);

        $query = $request->query->get('query');

        if (!$query) {
            throw new UnprocessableEntityHttpException('"query" is missing');
        }

        $roomIssues = $this->roomService->searchIssues($query, $roomId);

        return new JsonResponse($roomIssues);
    }

    #[Route('/rooms/{roomId}/issues', 'app_project_room_add_issue', methods: ['POST'])]
    public function addRoomIssue(Project $project, string $roomId, Request $request): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::ADD_ROOM_ISSUE, $project);

        $issueId = $request->get('issueId');
        if (!$issueId) {
            throw new UnprocessableEntityHttpException('"issueId" is missing');
        }

        $room = $this->findRoom($roomId);
        $issue = $this->findIssue($issueId);

        $roomEditor = $this->roomEditorFactory->create($room);

        $roomEditor->addIssue($issue);

        return new Response(status: 204);
    }

    #[Route('/rooms/{roomId}/issues/{issueId}/remove', 'app_project_room_remove_issue', methods: ['POST'])]
    public function removeRoomIssue(Project $project, string $roomId, string $issueId): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::REMOVE_ROOM_ISSUE, $project);

        $room = $this->findRoom($roomId);

        $roomIssue = $this->roomIssueRepository->findOneBy([
            'id' => $issueId,
        ]);
        if (!$roomIssue) {
            throw new NotFoundHttpException('Room issue not found');
        }

        $roomEditor = $this->roomEditorFactory->create($room);

        $roomEditor->removeIssue($roomIssue);

        return new Response(status: 204);
    }

    private function findIssue(string $issueId): Issue
    {
        $issue = $this->issueRepository->findOneBy([
            'id' => $issueId
        ]);

        if (!$issue) {
            throw new NotFoundHttpException('Issue not found');
        }

        return $issue;
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
