<?php

namespace App\Service\Sprint\BurndownChartService;

use DateTimeImmutable;

class BurndownChartRecord
{
    public function __construct(
        public string $date,
        public ?int $storyPoints
    ) {
    }

}
