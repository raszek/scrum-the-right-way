<?php

namespace App\Event;

interface EventInterface
{
    public function name(): string;

    public function toArray(): array;

    public static function fromArray(array $data): static;

}
