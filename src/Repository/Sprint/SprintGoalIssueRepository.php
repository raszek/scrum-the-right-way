<?php

namespace App\Repository\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Repository\QueryBuilder\QueryBuilder;
use App\Service\Position\ReorderService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SprintGoalIssue>
 */
class SprintGoalIssueRepository extends ServiceEntityRepository implements ReorderService
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SprintRepository $sprintRepository,
    ) {
        parent::__construct($registry, SprintGoalIssue::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function sprintGoalIssueQuery(SprintGoal $sprintGoal): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->where('sprintGoalIssue.sprintGoal = :sprintGoal')
            ->sqidParameter('sprintGoal', $sprintGoal->getId())
            ->orderBy('sprintGoalIssue.goalOrder', 'ASC');

        return $queryBuilder;
    }

    public function findCurrentSprintIssue(Issue $issue): ?SprintGoalIssue
    {
        $currentSprint = $this->sprintRepository->getCurrentSprint($issue->getProject());

        return $this->findSprintIssue($issue, $currentSprint);
    }

    public function findSprintIssue(Issue $issue, Sprint $sprint): ?SprintGoalIssue
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('sprintGoalIssue.issue = :issue')
            ->sqidParameter('issue', $issue->getId());

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findLastOrder(SprintGoal $sprintGoal): int
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->select('max(sprintGoalIssue.goalOrder)')
            ->andWhere('sprintGoalIssue.sprintGoal = :goal')
            ->sqidParameter('goal', $sprintGoal->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult() ?? 0;
    }

    /**
     * @param SprintGoalIssue $positionable
     * @return void
     */
    public function reorder($positionable): void
    {
        $query = $this->sprintGoalIssueQuery($positionable->getSprintGoal())->getQuery();

        $batchSize = 20;
        $i = 1;

        /**
         * @var SprintGoalIssue $issue
         */
        foreach ($query->toIterable() as $issue) {
            $issue->setOrder($i * $issue->getOrderSpace());

            if (($i % $batchSize) === 0) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear();
            }
            $i++;
        }

        $this->getEntityManager()->flush();
    }
}
