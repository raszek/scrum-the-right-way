<?php

namespace App\Repository\Sprint;

use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @extends ServiceEntityRepository<Sprint>
 */
class SprintRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sprint::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function getCurrentSprint(Project $project): Sprint
    {
        $currentSprint = $this->findOneBy([
            'project' => $project,
            'isCurrent' => true
        ]);

        if (!$currentSprint) {
            throw new RuntimeException('Current sprint not found.');
        }

        return $currentSprint;
    }

    public function getNextSprintNumber(Project $project): int
    {
        $queryBuilder = $this->createQueryBuilder('sprint');

        $queryBuilder
            ->select([
                'max(sprint.number) as number',
            ])
            ->where('sprint.project = :project')
            ->sqidParameter('project', $project->getId());

        $maxSprintNumberInProject = $queryBuilder->getQuery()->getSingleScalarResult();

        if (!$maxSprintNumberInProject) {
            return 1;
        }

        return $maxSprintNumberInProject + 1;
    }

    public function listQuery(Project $project): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('sprint');

        $queryBuilder
            ->where('sprint.project = :project')
            ->sqidParameter('project', $project->getId())
            ->andWhere('sprint.isCurrent = false')
            ->orderBy('sprint.number', 'DESC');

        return $queryBuilder;
    }

    public function getSprintIssues(Sprint $sprint): Sprint
    {
        $queryBuilder = $this->createQueryBuilder('sprint');

        $queryBuilder
            ->addSelect('sprintGoal')
            ->addSelect('sprintGoalIssue')
            ->addSelect('issue')
            ->join('sprint.sprintGoals', 'sprintGoal')
            ->join('sprintGoal.sprintGoalIssues', 'sprintGoalIssue')
            ->join('sprintGoalIssue.issue', 'issue')
            ->where('sprint.id = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->orderBy('sprintGoal.sprintOrder', 'ASC')
            ->addOrderBy('sprintGoalIssue.goalOrder', 'ASC');

        return $queryBuilder->getQuery()->getSingleResult();
    }

}
