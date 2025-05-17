<?php

namespace App\Entity\Thread;

readonly class ThreadRecord
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public string $fullName,
        public string $status,
        public int $postCount,
        public string $updatedAt
    ) {
    }

}
