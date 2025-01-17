<?php

namespace App\Table;

use Knp\Component\Pager\PaginatorInterface;

readonly class TableFactory
{

    public function __construct(
        private PaginatorInterface $paginator
    ) {
    }

    public function create(TableDefinition $tableDefinition, QueryParams $queryParams): Table
    {
        $pagination = $this->paginator->paginate(
            target: $tableDefinition->getQuery(),
            page: $queryParams->page,
            limit: $queryParams->limit,
            options: [
                PaginatorInterface::DEFAULT_SORT_FIELD_NAME => $queryParams->defaultSortField,
                PaginatorInterface::DEFAULT_SORT_DIRECTION => $queryParams->defaultSortDirection,
            ]
        );

        return new Table($tableDefinition, $pagination);
    }

}
