<?php

namespace App\Controller\Kanban;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Enum\Kanban\KanbanFilterEnum;
use App\Security\Voter\IssueVoter;
use App\Service\Kanban\KanbanAccess;
use App\Service\Kanban\KanbanService;
use App\Service\Kanban\KanbanSession;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class KanbanController extends Controller
{

    public function __construct(
        private readonly KanbanService $kanbanService,
        private readonly KanbanAccess $kanbanAccess,
        private readonly KanbanSession $kanbanSession,
    ) {
    }

    #[Route('/kanban', name: 'app_project_kanban')]
    public function kanban(Project $project): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::KANBAN_VIEW, $project);

        $error = $this->kanbanAccess->kanbanViewAccessError($project);
        if ($error) {
            throw new BadRequestHttpException($error);
        }

        $filter = $this->kanbanSession->getFilter();

        $columns = $this->kanbanService->getColumns($project, $filter);

        return $this->render('kanban/kanban.html.twig', [
            'project' => $project,
            'columns' => $columns,
            'filter' => $filter,
        ]);
    }

    #[Route('/kanban/columns/{filter}', name: 'app_project_kanban_columns')]
    public function columns(Project $project, string $filter): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::KANBAN_VIEW, $project);

        $filterEnum = KanbanFilterEnum::tryFrom($filter);
        if (!$filterEnum) {
            throw new BadRequestHttpException(
                sprintf('Invalid filter "%s". Possible options: [%s]', $filter, implode(',', KanbanFilterEnum::cases()))
            );
        }

        $error = $this->kanbanAccess->kanbanViewAccessError($project);
        if ($error) {
            throw new BadRequestHttpException($error);
        }

        $this->kanbanSession->setFilter($filterEnum);
        $columns = $this->kanbanService->getColumns($project, $filterEnum);

        return $this->render('kanban/kanban_columns.html.twig', [
            'project' => $project,
            'columns' => $columns,
        ]);
    }
}
