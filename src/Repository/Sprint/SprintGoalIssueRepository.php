<?php

namespace App\Repository\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoalIssue;
use App\Repository\QueryBuilder\QueryBuilder;
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

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function findSprintIssue(Issue $issue, Sprint $sprint): ?SprintGoalIssue
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('sprintGoalIssue.issue = :issue')
            ->setParameter('issue', $issue);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
