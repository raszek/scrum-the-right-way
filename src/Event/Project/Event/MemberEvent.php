<?php

namespace App\Event\Project\Event;

readonly class MemberEvent
{
    public function __construct(
        public int $userId
    ) {
    }


    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            userId: $data['userId'],
        );
    }

}
