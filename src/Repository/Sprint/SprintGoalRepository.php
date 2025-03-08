<?php

namespace App\Repository\Sprint;

use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SprintGoal>
 */
class SprintGoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SprintGoal::class);
    }

    /**
     * @param Sprint $sprint
     * @return SprintGoal[]
     */
    public function getSprintGoals(Sprint $sprint): array
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoal');

        $queryBuilder
            ->select(['sprintGoal', 'sprintGoalIssues', 'issue'])
            ->leftJoin('sprintGoal.sprintGoalIssues', 'sprintGoalIssues')
            ->leftJoin('sprintGoalIssues.issue', 'issue')
            ->where('sprintGoal.sprint = :sprint')
            ->setParameter('sprint', $sprint->getId()->integerId());

        return $queryBuilder->getQuery()->getResult();
    }

}
