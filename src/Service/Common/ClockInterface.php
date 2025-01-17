<?php

namespace App\Service\Common;

use Carbon\CarbonImmutable;

interface ClockInterface
{

    public function now(): CarbonImmutable;
}
