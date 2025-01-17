<?php

namespace App\Table;

use DateTimeInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

readonly class Table
{

    private PropertyAccessor $propertyAccessor;

    public function __construct(
        private TableDefinition $tableDefinition,
        private PaginationInterface $pagination
    ) {
        $this->propertyAccessor = new PropertyAccessor();
    }

    public function printItem(mixed $object, TableColumn $column): ?string
    {
        if ($column->formatCallback) {
            return ($column->formatCallback)($object);
        }

        $value = $this->propertyAccessor->getValue($object, $column->field);

        if ($value instanceof DateTimeInterface) {
            return $value->format('F j, Y H:i');
        }

        return $value;
    }

    public function getPagination(): PaginationInterface
    {
        return $this->pagination;
    }

    /**
     * @return iterable
     */
    public function getItems(): iterable
    {
        return $this->pagination->getItems();
    }

    /**
     * @return TableColumn[]
     */
    public function getColumns(): array
    {
        return $this->tableDefinition->getColumns();
    }

    public function columnCount(): int
    {
        return count($this->getColumns());
    }

    public function isEmpty(): bool
    {
        return $this->pagination->getTotalItemCount() === 0;
    }

}
