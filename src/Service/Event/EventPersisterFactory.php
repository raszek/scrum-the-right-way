<?php

namespace App\Service\Event;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class EventPersisterFactory
{

    public function __construct(
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(Project $project, User $user): EventPersister
    {
        return new EventPersister(
            project: $project,
            user: $user,
            clock: $this->clock,
            entityManager: $this->entityManager
        );
    }

}
