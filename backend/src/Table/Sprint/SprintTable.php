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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SprintTable
{

    public function __construct(
        private SprintRepository $sprintRepository,
        private TableFactory $tableFactory,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function create(Project $project, QueryParams $queryParams): Table
    {
        $query = $this->sprintRepository->listQuery($project);

        $definition = new TableDefinition($query);

        $definition->addColumn(new TableColumn(
            field: 'number',
            label: 'Sprint number',
            sortField: 'sprint.number',
            stripTags: '<a>',
            formatCallback: fn(Sprint $sprint) => sprintf('<a href="%s">%s</a>',
                $this->urlGenerator->generate('app_project_sprint_view', [
                    'id' => $project->getId(),
                    'sprintId' => $sprint->getId()
                ]),
                'Sprint ' . $sprint->getNumber()
            )
        ));

        return $this->tableFactory->create($definition, $queryParams);
    }

}
