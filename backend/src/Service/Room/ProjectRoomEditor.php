<?php

namespace App\Service\Room;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Room\Room;
use App\Entity\Room\RoomIssue;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

readonly class ProjectRoomEditor
{

    public function __construct(
        private Project $project,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @param Issue[] $issues
     * @return Room
     */
    public function create(array $issues): Room
    {
        $createdRoom = new Room(
            project: $this->project,
            createdAt: $this->clock->now(),
        );

        $this->entityManager->persist($createdRoom);

        foreach ($issues as $issue) {
            $this->addIssue($issue, $createdRoom);
        }

        if (count($this->entityManager->getUnitOfWork()->getScheduledEntityInsertions()) < 1) {
            throw new DomainException('Cannot create room without issues.');
        }

        $this->entityManager->flush();

        return $createdRoom;
    }

    private function addIssue(Issue $issue, Room $room): void
    {
        if ($issue->isFeature()) {
            $this->addFeatureSubIssues($issue, $room);
        } else {
            if ($issue->isSubIssue()) {
                throw new DomainException('Cannot add sub issue to room.');
            }

            $roomIssue = new RoomIssue(
                room: $room,
                issue: $issue,
            );

            $this->entityManager->persist($roomIssue);
        }
    }

    private function addFeatureSubIssues(Issue $issue, Room $room): void
    {
        foreach ($issue->getSubIssues() as $subIssue) {
            $roomIssue = new RoomIssue(
                room: $room,
                issue: $subIssue,
            );

            $this->entityManager->persist($roomIssue);
        }
    }

}
