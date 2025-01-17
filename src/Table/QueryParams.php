<?php

namespace App\Table;

use Symfony\Component\HttpFoundation\Request;

class QueryParams
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?string $sortField = null,
        public ?string $sortDirection = null,
        public ?string $defaultSortField = null,
        public string $defaultSortDirection = 'asc',
        private mixed $filters = null,
    ) {
    }

    public function setFilters(mixed $filters): void
    {
        $this->filters = $filters;
    }

    public function getFilters(): mixed
    {
        return $this->filters;
    }

    public static function fromRequest(Request $request): QueryParams
    {
        return new static(
            page: $request->query->get('page', 1),
            limit: $request->query->get('limit', 20),
            sortField: $request->query->get('sort'),
            sortDirection: $request->query->get('sort-direction'),
        );
    }

}
