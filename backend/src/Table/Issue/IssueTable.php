<?php

namespace App\Table\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Repository\Issue\IssueRepository;
use App\Table\QueryParams;
use App\Table\Table;
use App\Table\TableDefinition;
use App\Table\TableColumn;
use App\Table\TableFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class IssueTable
{

    public function __construct(
        private IssueRepository $issueRepository,
        private TableFactory $tableFactory,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function create(Project $project, QueryParams $queryParams): Table
    {
        $query = $this->issueRepository->issueQuery($project, $queryParams);

        $definition = new TableDefinition($query);

        $definition->addColumn(new TableColumn(
            field: 'title',
            label: 'Title',
            sortField: 'issue.title',
            stripTags: '<a>',
            formatCallback: fn(Issue $issue) => sprintf('<a href="%s">%s</a>',
                $this->urlGenerator->generate('app_project_issue_view', [
                    'id' => $project->getId(),
                    'issueCode' => $issue->getCode()
                ]),
                $issue->getShortTitle()
            )
        ));
        $definition->addColumn(new TableColumn(
            field: 'code',
            label: 'Number',
            sortField: 'issue.number'
        ));
        $definition->addColumn(new TableColumn(
            field: 'issueColumn.label',
            label: 'Column',
            sortField: 'column.id'
        ));
        $definition->addColumn(new TableColumn(
            field: 'type.label',
            label: 'Type',
            sortField: 'type.id',
        ));
        $definition->addColumn(new TableColumn(
            field: 'createdBy.fullName',
            label: 'Created By',
            sortField: 'createdBy.id'
        ));
        $definition->addColumn(new TableColumn(
            field: 'createdAt',
            label: 'Created at',
            sortField: 'issue.createdAt'
        ));
        $definition->addColumn(new TableColumn(
            field: 'updatedAt',
            label: 'Updated at',
            sortField: 'issue.updatedAt'
        ));

        return $this->tableFactory->create($definition, $queryParams);
    }

}
