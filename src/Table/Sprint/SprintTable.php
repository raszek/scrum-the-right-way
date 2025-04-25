<?php

namespace App\Table\Sprint;

use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Repository\Sprint\SprintRepository;
use App\Table\QueryParams;
use App\Table\Table;
use App\Table\TableColumn;
use App\Table\TableDefinition;
use App\Table\TableFactory;

readonly class SprintTable
{

    public function __construct(
        private SprintRepository $sprintRepository,
        private TableFactory $tableFactory,
    ) {
    }

    public function create(Project $project, QueryParams $queryParams): Table
    {
        $query = $this->sprintRepository->listQuery($project);

        $definition = new TableDefinition($query);

        $definition->addColumn(new TableColumn(
            field: 'number',
            label: 'Number',
            sortField: 'sprint.number',
            formatCallback: fn(Sprint $sprint) => 'Sprint ' . $sprint->getNumber()
        ));

        return $this->tableFactory->create($definition, $queryParams);
    }

}
