<?php

namespace App\Action\Kanban;

use App\Enum\Issue\IssueColumnEnum;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Exception\Kanban\CannotChangeKanbanColumnException;
use App\Service\Issue\IssueEditor\IssueEditorFactory;

readonly class MoveKanbanIssueAction
{

    public function __construct(
        private IssueEditorFactory $issueEditorFactory,
    ) {
    }

    /**
     * @param MoveKanbanIssueActionData $data
     * @return void
     * @throws OutOfBoundPositionException
     * @throws CannotChangeKanbanColumnException
     */
    public function execute(MoveKanbanIssueActionData $data): void
    {
        $issueEditor = $this->issueEditorFactory->create($data->issue, $data->user);

        $column = IssueColumnEnum::fromKey($data->column);

        $issueEditor->changeKanbanColumn($column, $data->position);
    }
}
