<?php

namespace App\Service\Position;

interface Positionable
{

    public function getOrder(): int;

    public function setOrder(int $order): void;

    public function getOrderSpace(): int;

}
