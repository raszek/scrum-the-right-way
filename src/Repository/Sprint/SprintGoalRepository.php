<?php

namespace App\Repository\Sprint;

use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Repository\QueryBuilder\QueryBuilder;
use App\Service\Position\ReorderService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SprintGoal>
 */
class SprintGoalRepository extends ServiceEntityRepository implements ReorderService
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SprintGoal::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function sprintGoalQuery(Sprint $sprint): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoal');

        $queryBuilder
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->orderBy('sprintGoal.sprintOrder', 'ASC');

        return $queryBuilder;
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
            ->setParameter('sprint', $sprint->getId()->integerId())
            ->orderBy('sprintGoal.sprintOrder', 'ASC')
            ->addOrderBy('sprintGoalIssues.goalOrder', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findLastOrder(Sprint $sprint): int
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoal');

        $queryBuilder
            ->select('max(sprintGoal.sprintOrder)')
            ->andWhere('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult() ?? 0;
    }

    /**
     * @param SprintGoal $positionable
     * @return void
     */
    public function reorder($positionable): void
    {
        $query = $this->sprintGoalQuery($positionable->getSprint())->getQuery();

        $batchSize = 20;
        $i = 1;
        /**
         * @var SprintGoal $sprintGoal
         */
        foreach ($query->toIterable() as $sprintGoal) {
            $sprintGoal->setOrder($i * $sprintGoal->getOrderSpace());

            if (($i % $batchSize) === 0) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear();
            }
            $i++;
        }

        $this->getEntityManager()->flush();
    }
}
