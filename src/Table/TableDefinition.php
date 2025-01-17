<?php

namespace App\Table;

use App\Repository\QueryBuilder\QueryBuilder;

class TableDefinition
{

    /**
     * @var TableColumn[]
     */
    private array $columns;

    public function __construct(
        private readonly QueryBuilder $queryBuilder
    ) {
    }

    /**
     * @return TableColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function addColumn(TableColumn $column): void
    {
        $this->columns[] = $column;
    }

    public function getQuery(): QueryBuilder
    {
        return $this->queryBuilder;
    }

}
