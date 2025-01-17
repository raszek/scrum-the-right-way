<?php

namespace App\Event\Project\Event;

readonly class RoleEvent
{
    public function __construct(
        public int $userId,
        public string $projectRole
    ) {
    }


    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'projectRole' => $this->projectRole
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            userId: $data['userId'],
            projectRole: $data['projectRole']
        );
    }

}
