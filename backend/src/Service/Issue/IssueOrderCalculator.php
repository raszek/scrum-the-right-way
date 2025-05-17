<?php

namespace App\Service\Issue;

use App\Exception\Issue\NoOrderSpaceException;

class IssueOrderCalculator
{

    public static function findOrderBetween(int $firstOrder, int $secondOrder): int
    {
        if (abs($firstOrder - $secondOrder) <= 1) {
            throw new NoOrderSpaceException('No order space exception');
        }

        return ($firstOrder + $secondOrder) / 2;
    }
}
