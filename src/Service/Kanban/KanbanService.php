<?php

namespace App\Service\Kanban;

use App\Entity\Project\Project;
use App\Enum\Issue\IssueColumnEnum;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
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
    public function getColumns(Project $project): array
    {
        $columns = [];

        foreach (IssueColumnEnum::kanbanColumns() as $issueColumn) {
            $columnQuery = $this->issueRepository->columnQuery(
                $project,
                $this->issueColumnRepository->fromEnum($issueColumn)
            );

            $columnQuery->setMaxResults(100);

            $columns[] = new KanbanColumn(
                name: $issueColumn->label(),
                items: $columnQuery->getQuery()->getResult(),
            );
        }

        return $columns;
    }

}
