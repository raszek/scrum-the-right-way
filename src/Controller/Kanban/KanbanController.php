<?php

namespace App\Controller\Kanban;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Security\Voter\IssueVoter;
use App\Service\Kanban\KanbanAccess;
use App\Service\Kanban\KanbanService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class KanbanController extends Controller
{

    public function __construct(
        private readonly KanbanService $kanbanService,
    ) {
    }

    #[Route('/kanban', name: 'app_project_kanban')]
    public function kanban(Project $project, KanbanAccess $kanbanAccess): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::KANBAN_VIEW, $project);

        $error = $kanbanAccess->kanbanViewAccessError($project);
        if ($error) {
            throw new BadRequestHttpException($error);
        }

        $columns = $this->kanbanService->getColumns($project);

        return $this->render('issue/kanban.html.twig', [
            'project' => $project,
            'columns' => $columns,
        ]);
    }
}
