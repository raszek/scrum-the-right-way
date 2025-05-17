<?php

namespace App\Service\Common;

use Carbon\CarbonImmutable;

class Clock implements ClockInterface
{

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

}
