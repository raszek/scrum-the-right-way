<?php

namespace App\Event;

use DateTimeImmutable;

readonly class EventRecord
{

    public function __construct(
        public int $id,
        public string $content,
        public DateTimeImmutable $createdAt
    ) {
    }

}
