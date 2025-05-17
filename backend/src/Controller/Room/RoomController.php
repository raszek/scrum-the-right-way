<?php

namespace App\Controller\Room;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Security\Voter\RoomVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class RoomController extends Controller
{

    #[Route('/rooms/{roomId}', 'app_project_room_view')]
    public function view(Project $project): Response
    {
        $this->denyAccessUnlessGranted(RoomVoter::VIEW_ROOM, $project);

        return $this->render('room/view.html.twig', [
            'project' => $project,
        ]);
    }
}
