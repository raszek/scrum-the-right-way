<?php

namespace App\Service\Room;

use App\Entity\Issue\Issue;
use App\Entity\Room\Room;
use App\Entity\Room\RoomIssue;
use App\Exception\Room\CannotAddRoomIssueException;
use App\Repository\Room\RoomIssueRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RoomEditor
{

    public function __construct(
        private Room $room,
        private EntityManagerInterface $entityManager,
        private RoomIssueRepository $roomIssueRepository
    ) {
    }

    public function addIssue(Issue $issue): void
    {
        if ($issue->isFeature()) {
            throw new CannotAddRoomIssueException('Cannot add feature to room.');
        }

        if (!$this->room->getProject()->getId()->equals($issue->getProject()->getId())) {
            throw new CannotAddRoomIssueException('Issue and room must belong to the same project.');
        }

        if ($this->roomHasIssue($issue)) {
            throw new CannotAddRoomIssueException('Room already has this issue.');
        }

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

    private function roomHasIssue(Issue $issue): bool
    {
        $foundIssue = $this->roomIssueRepository->findOneBy([
            'issue' => $issue,
            'room' => $this->room,
        ]);

        return $foundIssue !== null;
    }
}
