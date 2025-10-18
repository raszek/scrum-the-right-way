<?php

namespace App\Controller\Kanban;

use App\Action\Kanban\MoveKanbanIssueAction;
use App\Action\Kanban\MoveKanbanIssueActionData;
use App\Controller\Issue\CommonIssueController;
use App\Entity\Project\Project;
use App\Enum\Kanban\KanbanFilterEnum;
use App\Form\Kanban\MoveIssueForm;
use App\Formulate\CannotLoadFormException;
use App\Formulate\CannotValidateFormException;
use App\Helper\StimulusHelper;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\KanbanVoter;
use App\Service\Kanban\KanbanAccess;
use App\Service\Kanban\KanbanService;
use App\Service\Kanban\KanbanSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            'disabledSort' => $this->isDisabledSort($filter)
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
            'disabledSort' => $this->isDisabledSort($filterEnum),
        ]);
    }

    #[Route('/kanban/issues/{issueCode}/move', name: 'app_project_kanban_issue_move')]
    public function moveIssue(
        Project $project,
        string $issueCode,
        MoveIssueForm $moveIssueForm,
        MoveKanbanIssueAction $moveKanbanIssueAction,
        Request $request
    ): Response
    {
        $this->denyAccessUnlessGranted(KanbanVoter::KANBAN_MOVE_ISSUE, $project);

        $form = $moveIssueForm->create();

        $this->validate($form, $request);

        $issue = $this->findIssue($issueCode, $project);

        $data = $form->getData();

        $moveKanbanIssueAction->execute(
            new MoveKanbanIssueActionData(
                issue: $issue,
                user: $this->getLoggedInUser(),
                position: $data->position,
                column: $data->column,
            )
        );

        return new Response(status: 204);
    }

    private function isDisabledSort(KanbanFilterEnum $enum): string
    {
        return StimulusHelper::boolean($enum === KanbanFilterEnum::Big);
    }

    private function getInProgressIssueData(): string
    {
        $inProgressIssue = $this->getLoggedInUser()->getInProgressIssue();

        if (!$inProgressIssue) {
            return StimulusHelper::nullObject();
        }

        return StimulusHelper::object([
            'id' => $inProgressIssue->getId()->get(),
            'url' => $this->generateUrl('app_project_issue_view', [
                'id' => $inProgressIssue->getProject()->getId(),
                'issueCode' => $inProgressIssue->getCode()
            ]),
            'currentColumn' => $inProgressIssue->getIssueColumn()->getKey()
        ]);
    }
}
