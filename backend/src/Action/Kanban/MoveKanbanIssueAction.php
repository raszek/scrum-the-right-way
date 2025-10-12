<?php

namespace App\Action\Kanban;

use App\Enum\Issue\IssueColumnEnum;
use App\Service\Issue\IssueEditor\IssueEditorFactory;

readonly class MoveKanbanIssueAction
{

    public function __construct(
        private IssueEditorFactory $issueEditorFactory,
    ) {
    }

    public function execute(MoveKanbanIssueActionData $data): void
    {
        $issueEditor = $this->issueEditorFactory->create($data->issue, $data->user);

        $column = IssueColumnEnum::fromKey($data->column);

        $issueEditor->changeKanbanColumn($column, $data->position);
    }
}
