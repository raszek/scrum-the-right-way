<?php

namespace App\Service\Room;

use App\Entity\Issue\Issue;
use App\Entity\Room\Room;
use App\Entity\Room\RoomIssue;
use Doctrine\ORM\EntityManagerInterface;

readonly class RoomEditor
{

    public function __construct(
        private Room $room,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function addIssue(Issue $issue): void
    {
        $roomIssue = new RoomIssue(
            room: $this->room,
            issue: $issue,
        );

        $this->entityManager->persist($roomIssue);

        $this->room->addRoomIssue($roomIssue);

        $this->entityManager->flush();
    }

    public function removeIssue(RoomIssue $issue): void
    {
        $this->room->removeIssue($issue);

        $this->entityManager->remove($issue);

        $this->entityManager->flush();
    }

}
