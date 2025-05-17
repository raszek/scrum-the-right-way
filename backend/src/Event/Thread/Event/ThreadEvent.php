<?php

namespace App\Event\Thread\Event;

readonly class ThreadEvent
{
    public function __construct(
        public int $threadId
    ) {
    }

    public function toArray(): array
    {
        return [
            'threadId' => $this->threadId
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            threadId: $data['threadId']
        );
    }
}
