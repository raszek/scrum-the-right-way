<?php

namespace App\Service\Kanban;

use App\Entity\Issue\IssueColumn;
use App\Entity\Project\Project;
use App\Enum\Issue\IssueColumnEnum;
use App\Enum\Kanban\KanbanFilterEnum;
use App\Helper\StimulusHelper;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\QueryBuilder\QueryBuilder;
use App\View\Kanban\KanbanColumn;

readonly class KanbanService
{

    public function __construct(
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
    ) {
    }

    /**
     * @return KanbanColumn[]
     */
    public function getColumns(Project $project, KanbanFilterEnum $filter = KanbanFilterEnum::Big): array
    {
        $columns = [];

        foreach (IssueColumnEnum::kanbanColumns() as $issueColumn) {
            $query = $this->getColumnQuery(
                filter: $filter,
                project: $project,
                column: $this->issueColumnRepository->fromEnum($issueColumn),
            );

            $columns[] = new KanbanColumn(
                name: $issueColumn->label(),
                key: $issueColumn->key(),
                items: $query->getQuery()->getResult(),
                disabled: $this->isColumnDisabled($filter, $issueColumn),
            );
        }

        return $columns;
    }

    private function getColumnQuery(KanbanFilterEnum $filter, Project $project, IssueColumn $column): QueryBuilder
    {
        if ($filter === KanbanFilterEnum::Big) {
            return $this->issueRepository->bigColumnQuery($project, $column);
        }

        return $this->issueRepository->smallColumnQuery($project, $column);
    }

    private function isColumnDisabled(KanbanFilterEnum $filterEnum, IssueColumnEnum $column): bool
    {
        if ($filterEnum === KanbanFilterEnum::Big) {
            return true;
        }

        return $column === IssueColumnEnum::Finished;
    }

}
