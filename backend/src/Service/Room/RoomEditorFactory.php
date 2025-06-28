<?php

namespace App\Service\Room;

use App\Entity\Room\Room;
use Doctrine\ORM\EntityManagerInterface;

readonly class RoomEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(Room $room): RoomEditor
    {
        return new RoomEditor(
            room: $room,
            entityManager: $this->entityManager,
        );
    }
}
