<?php

namespace App\Controller\Kanban;

use App\Controller\Issue\CommonIssueController;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Enum\Issue\IssueColumnEnum;
use App\Enum\Kanban\KanbanFilterEnum;
use App\Form\Kanban\MoveIssueForm;
use App\Helper\JsonHelper;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\KanbanVoter;
use App\Service\Issue\IssueEditor\IssueEditorFactory;
use App\Service\Kanban\KanbanAccess;
use App\Service\Kanban\KanbanService;
use App\Service\Kanban\KanbanSession;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class KanbanController extends CommonIssueController
{

    public function __construct(
        private readonly KanbanService $kanbanService,
        private readonly KanbanAccess $kanbanAccess,
        private readonly KanbanSession $kanbanSession,
        private readonly IssueRepository $issueRepository,
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/kanban', name: 'app_project_kanban')]
    public function kanban(Project $project): Response
    {
        $this->denyAccessUnlessGranted(KanbanVoter::KANBAN_VIEW, $project);

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
            'currentIssue' => $this->getInProgressIssueData(),
        ]);
    }

    #[Route('/kanban/columns/{filter}', name: 'app_project_kanban_columns')]
    public function columns(Project $project, string $filter): Response
    {
        $this->denyAccessUnlessGranted(KanbanVoter::KANBAN_VIEW, $project);

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
            'currentIssue' => $this->getInProgressIssueData(),
        ]);
    }

    #[Route('/kanban/issues/{issueCode}/move', name: 'app_project_kanban_issue_move')]
    public function moveIssue(
        Project $project,
        string $issueCode,
        #[MapRequestPayload] MoveIssueForm $form,
        IssueEditorFactory $factory,
    ): Response
    {
        $this->denyAccessUnlessGranted(KanbanVoter::KANBAN_MOVE_ISSUE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $issueEditor = $factory->create($issue, $this->getLoggedInUser());

        $column = IssueColumnEnum::fromKey($form->column);

        $issueEditor->changeKanbanColumn($column);

        $issueEditor->sort($form->position);

        return new Response(status: 204);
    }

    private function getInProgressIssueData(): string
    {
        $inProgressIssue = $this->getLoggedInUser()->getInProgressIssue();

        if (!$inProgressIssue) {
            return '{}';
        }

        return JsonHelper::encode([
            'id' => $inProgressIssue->getId()->get(),
            'url' => $this->generateUrl('app_project_issue_view', [
                'id' => $inProgressIssue->getProject()->getId(),
                'issueCode' => $inProgressIssue->getCode()
            ]),
            'currentColumn' => $inProgressIssue->getIssueColumn()->getKey()
        ]);
    }
}
