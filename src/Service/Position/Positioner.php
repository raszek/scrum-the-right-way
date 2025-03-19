<?php

namespace App\Service\Position;

use App\Exception\Issue\NoOrderSpaceException;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Repository\QueryBuilder\QueryBuilder;

readonly class Positioner
{

    public function __construct(
        private QueryBuilder $query,
        private Positionable $positioned,
        private ReorderService $reorderService
    ) {
    }

    /**
     * @param int $position
     * @return void
     * @throws OutOfBoundPositionException
     */
    public function setPosition(int $position): void
    {
        $query = $this->query;

        $isFirstPosition = $position <= 1;
        if ($isFirstPosition) {
            $query->setMaxResults(1);
        } else {
            $query->setFirstResult($position - 2);
            $query->setMaxResults(2);
        }

        $positionableElements = $query->getQuery()->getResult();

        try {
            $order = $this->calculateOrder($positionableElements, $isFirstPosition);
            $this->positioned->setOrder($order);
        } catch (NoOrderSpaceException) {
            $this->reorderService->reorder($this->positioned);
            $this->setPosition($position);
        }
    }

    /**
     * @param Positionable[] $records
     * @param bool $isFirstPosition
     * @return int
     * @throws NoOrderSpaceException
     * @throws OutOfBoundPositionException
     */
    public function calculateOrder(array $records, bool $isFirstPosition): int
    {
        if (count($records) === 0) {
            if ($isFirstPosition) {
                return $this->positioned->getOrderSpace();
            }

            throw new OutOfBoundPositionException('Position number is bigger than issue count in the column');
        }

        if (count($records) === 1) {
            if ($isFirstPosition) {
                return $this->findOrderBetween(0, $records[0]->getOrder());
            } else {
                return $records[0]->getOrder() + $this->positioned->getOrderSpace();
            }
        }

        return $this->findOrderBetween($records[0]->getOrder(), $records[1]->getOrder());
    }

    public function findOrderBetween(int $firstOrder, int $secondOrder): int
    {
        if (abs($firstOrder - $secondOrder) <= 1) {
            throw new NoOrderSpaceException('No order space exception');
        }

        return ($firstOrder + $secondOrder) / 2;
    }
}

