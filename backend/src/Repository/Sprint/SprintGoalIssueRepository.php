<?php

namespace App\Repository\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Enum\Issue\IssueTypeEnum;
use App\Repository\QueryBuilder\QueryBuilder;
use App\Service\Position\ReorderService;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
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

    /**
     * @return SprintGoalIssue[]
     */
    public function getLatestDoneIssues(Sprint $sprint): array
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->addSelect('issue')
            ->join('sprintGoalIssue.issue', 'issue')
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('sprintGoalIssue.finishedAt is not null')
            ->orderBy('sprintGoalIssue.finishedAt', 'DESC')
            ->setMaxResults(5);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getCurrentSprintStoryPoints(Sprint $sprint): int
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->select('sum(issue.storyPoints)')
            ->join('sprintGoalIssue.issue', 'issue')
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('issue.type in (:issueTypeIds)')
            ->setParameter('issueTypeIds', [IssueTypeEnum::Issue->value, IssueTypeEnum::SubIssue->value]);

        return $queryBuilder->getQuery()->getSingleScalarResult() ?? 0;
    }

    public function getHistorySprintStoryPoints(Sprint $sprint): int
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->select('sum(sprintGoalIssue.storyPoints)')
            ->join('sprintGoalIssue.issue', 'issue')
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('issue.type in (:issueTypeIds)')
            ->setParameter('issueTypeIds', [IssueTypeEnum::Issue->value, IssueTypeEnum::SubIssue->value]);

        return $queryBuilder->getQuery()->getSingleScalarResult() ?? 0;
    }

    public function getCurrentSprintGroupedStoryPoints(Sprint $sprint, DateTimeImmutable $endDate): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('sum(i.story_points) as storyPoints', "date(sgi.finished_at) as finishedDay")
            ->from('sprint_goal_issue', 'sgi')
            ->innerJoin('sgi', 'sprint_goal', 'sg', 'sg.id = sgi.sprint_goal_id')
            ->innerJoin('sgi', 'issue', 'i', 'i.id = sgi.issue_id')
            ->where('sg.sprint_id = :sprintId')
            ->setParameter('sprintId', $sprint->getId()->integerId())
            ->andWhere('sgi.finished_at >= :startDate')
            ->setParameter('startDate', $sprint->getStartedAt()->format('Y-m-d'))
            ->andWhere('sgi.finished_at <= :endDate')
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->andWhere('i.type_id in (:typeIds)')
            ->setParameter('typeIds', [IssueTypeEnum::Issue->value, IssueTypeEnum::SubIssue->value], ArrayParameterType::INTEGER)
            ->groupBy('finishedDay')
            ->orderBy('finishedDay', 'ASC')
        ;

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function getHistorySprintGroupedStoryPoints(Sprint $sprint): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('sum(sgi.story_points) as storyPoints', "date(sgi.finished_at) as finishedDay")
            ->from('sprint_goal_issue', 'sgi')
            ->innerJoin('sgi', 'sprint_goal', 'sg', 'sg.id = sgi.sprint_goal_id')
            ->innerJoin('sgi', 'issue', 'i', 'i.id = sgi.issue_id')
            ->where('sg.sprint_id = :sprintId')
            ->setParameter('sprintId', $sprint->getId()->integerId())
            ->andWhere('sgi.finished_at >= :startDate')
            ->setParameter('startDate', $sprint->getStartedAt()->format('Y-m-d'))
            ->andWhere('sgi.finished_at <= :endDate')
            ->setParameter('endDate', $sprint->getEndedAt()->format('Y-m-d'))
            ->andWhere('i.type_id in (:typeIds)')
            ->setParameter('typeIds', [IssueTypeEnum::Issue->value, IssueTypeEnum::SubIssue->value], ArrayParameterType::INTEGER)
            ->groupBy('finishedDay')
            ->orderBy('finishedDay', 'ASC')
        ;

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function sprintGoalIssueQuery(SprintGoal $sprintGoal): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->join('sprintGoalIssue.issue', 'issue')
            ->where('sprintGoalIssue.sprintGoal = :sprintGoal')
            ->sqidParameter('sprintGoal', $sprintGoal->getId())
            ->andWhere('issue.type <> :issueTypeId')
            ->setParameter('issueTypeId', IssueTypeEnum::SubIssue->value)
            ->orderBy('sprintGoalIssue.goalOrder', 'ASC');

        return $queryBuilder;
    }

    public function countNonEstimatedStoryPointsIssues(Sprint $sprint): int
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->select('count(sprintGoalIssue.id)')
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->join('sprintGoalIssue.issue', 'issue')
            ->where('issue.type in (:typeIds)')
            ->setParameter('typeIds', [IssueTypeEnum::Issue->value, IssueTypeEnum::SubIssue->value])
            ->andWhere('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('issue.storyPoints is null');

        return $queryBuilder->getQuery()->getSingleScalarResult() ?? 0;
    }

    /**
     * @return SprintGoalIssue[]
     */
    public function findFeatureSubIssues(SprintGoalIssue $sprintGoalIssue): array
    {
        $ids = $sprintGoalIssue->getIssue()
            ->getSubIssues()
            ->map(fn(Issue $subIssue) => $subIssue->getId()->integerId())
            ->toArray();

        return $this->findBy([
            'issue' => $ids
        ]);
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

    /**
     * @param string[] $issueIds
     * @param Sprint $sprint
     * @return SprintGoalIssue[]
     */
    public function findSprintIssues(array $issueIds, Sprint $sprint): array
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId())
            ->andWhere('sprintGoalIssue.issue in (:issueIds)')
            ->sqidsParameter('issueIds', $issueIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Sprint $sprint
     * @return SprintGoalIssue[]
     */
    public function findAllSprintIssues(Sprint $sprint): array
    {
        $queryBuilder = $this->createQueryBuilder('sprintGoalIssue');

        $queryBuilder
            ->join('sprintGoalIssue.sprintGoal', 'sprintGoal')
            ->where('sprintGoal.sprint = :sprint')
            ->sqidParameter('sprint', $sprint->getId());

        return $queryBuilder->getQuery()->getResult();
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
