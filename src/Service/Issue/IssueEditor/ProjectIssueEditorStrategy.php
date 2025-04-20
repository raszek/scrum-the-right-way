<?php

namespace App\Service\Issue\IssueEditor;

use App\Enum\Issue\IssueColumnEnum;

interface ProjectIssueEditorStrategy
{

    public function changeKanbanColumn(IssueColumnEnum $column): void;

    public function getIssueEditableError(): ?string;
}
