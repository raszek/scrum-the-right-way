<?php

namespace App\Repository\Sprint;

use App\Entity\Sprint\SprintGoalIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SprintGoalIssue>
 */
class SprintGoalIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SprintGoalIssue::class);
    }
}
