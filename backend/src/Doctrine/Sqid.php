<?php

namespace App\Doctrine;

readonly class Sqid
{
    public function __construct(
        private string $sqid,
        private int $integerId
    ) {
    }

    public function get(): string
    {
        return $this->sqid;
    }

    public function integerId(): int
    {
        return $this->integerId;
    }

    public function equals(Sqid $id): bool
    {
        return $this->integerId === $id->integerId;
    }

    public function __toString(): string
    {
        return $this->sqid;
    }
}
