<?php

namespace App\Service\Room;

use App\Entity\Room\Room;
use App\Repository\Room\RoomIssueRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RoomEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoomIssueRepository $roomIssueRepository,
    ) {
    }

    public function create(Room $room): RoomEditor
    {
        return new RoomEditor(
            room: $room,
            entityManager: $this->entityManager,
            roomIssueRepository: $this->roomIssueRepository,
        );
    }
}
