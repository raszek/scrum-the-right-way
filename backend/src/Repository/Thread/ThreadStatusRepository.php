<?php

namespace App\Repository\Thread;

use App\Entity\Thread\ThreadStatus;
use App\Enum\Thread\ThreadStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThreadStatus>
 */
class ThreadStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadStatus::class);
    }

    public function openStatus(): ThreadStatus
    {
        return $this->findOneBy([
            'id' => ThreadStatusEnum::Open->value
        ]);
    }

    public function closedStatus(): ThreadStatus
    {
        return $this->findOneBy([
            'id' => ThreadStatusEnum::Closed->value
        ]);
    }
}
