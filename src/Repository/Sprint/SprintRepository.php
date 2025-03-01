<?php

namespace App\Repository\Sprint;

use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

}
