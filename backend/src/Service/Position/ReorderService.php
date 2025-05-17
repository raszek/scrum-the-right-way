<?php

namespace App\Service\Position;

interface ReorderService
{
    public function reorder($positionable): void;
}
