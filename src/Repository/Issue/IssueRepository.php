<?php

namespace App\Repository\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueColumn;
use App\Entity\Issue\IssueDependency;
use App\Entity\Issue\IssueType;
use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Enum\Issue\IssueColumnEnum;
use App\Form\Issue\IssueSearchForm;
use App\Helper\ArrayHelper;
use App\Repository\QueryBuilder\QueryBuilder;
use App\Service\Common\SqidService;
use App\Service\Position\ReorderService;
use App\Table\QueryParams;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Issue>
 */
class IssueRepository extends ServiceEntityRepository implements ReorderService
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly IssueColumnRepository $issueColumnRepository,
        private readonly IssueTypeRepository $issueTypeRepository,
        private readonly SqidService $sqidService
    ) {
        parent::__construct($registry, Issue::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function findByCode(string $code, Project $project): ?Issue
    {
        [$projectPrefix, $number] = explode('-', $code);

        if ($project->getCode() !== $projectPrefix) {
            return null;
        }

        return $this->findOneBy([
            'project' => $project,
            'number' => $number,
        ]);
    }

    /**
     * @return Issue[]
     */
    public function findByIds(array $issueIds, Project $project): array
    {
        $ids = $this->sqidService->decodeMany($issueIds);

        return $this->findBy([
            'id' => $ids,
            'project' => $project,
        ]);
    }

    public function backlogQuery(Project $project): QueryBuilder
    {
        $queryBuilder = $this->orderedColumnQuery($project, $this->issueColumnRepository->backlogColumn());

        $queryBuilder->andWhere('issue.type <> :type');
        $queryBuilder->setParameter('type', $this->issueTypeRepository->subIssueType());

        return $queryBuilder;
    }

    public function getNextIssueNumber(Project $project): int
    {
        $queryBuilder = $this->createQueryBuilder('issue');

        $queryBuilder
            ->select([
                'max(issue.number) as number',
            ])
            ->where('issue.project = :project')
            ->sqidParameter('project', $project->getId());

        $maxIssueIdInProject = $queryBuilder->getQuery()->getSingleScalarResult();

        return $maxIssueIdInProject + 1;
    }

    public function orderedColumnQuery(Project $project, IssueColumn $issueColumn): QueryBuilder
    {
        $columnQuery = $this->columnQuery($project, $issueColumn);

        $columnQuery->orderBy('issue.columnOrder', 'ASC');

        return $columnQuery;
    }

    public function columnQuery(Project $project, IssueColumn $issueColumn): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('issue');

        $queryBuilder
            ->where('issue.project = :project')
            ->andWhere('issue.issueColumn = :issueColumn');

        $queryBuilder->setParameter('issueColumn', $issueColumn);
        $queryBuilder->sqidParameter('project', $project->getId());

        return $queryBuilder;
    }

    public function kanbanColumnQuery(Project $project, IssueColumn $issueColumn): QueryBuilder
    {
        $columnQuery = $this->columnQuery($project, $issueColumn);
        $columnQuery->setMaxResults(100);
        $columnQuery->orderBy('issue.columnOrder', 'ASC');

        return $columnQuery;
    }

    public function bigColumnQuery(Project $project, IssueColumn $issueColumn): QueryBuilder
    {
        $kanbanColumnQuery = $this->kanbanColumnQuery($project, $issueColumn);
        $kanbanColumnQuery->andWhere('issue.type <> :type');
        $kanbanColumnQuery->setParameter('type', $this->issueTypeRepository->subIssueType());

        return $kanbanColumnQuery;
    }

    public function smallColumnQuery(Project $project, IssueColumn $issueColumn): QueryBuilder
    {
        $kanbanColumnQuery = $this->kanbanColumnQuery($project, $issueColumn);
        $kanbanColumnQuery->andWhere('issue.type <> :type');
        $kanbanColumnQuery->setParameter('type', $this->issueTypeRepository->featureType());

        return $kanbanColumnQuery;
    }

    public function featureIssueQuery(Issue $issue): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('issue');

        $queryBuilder
            ->where('issue.project = :project')
            ->andWhere('issue.parent = :parent')
            ->andWhere('issue.issueColumn <> :column')
            ->orderBy('issue.issueOrder', 'ASC');

        $queryBuilder->sqidParameter('project', $issue->getProject()->getId());
        $queryBuilder->sqidParameter('parent', $issue->getId());
        $queryBuilder->setParameter('column', IssueColumnEnum::Archived->value);

        return $queryBuilder;
    }

    public function featureSubIssues(Issue $issue): array
    {
        if (!$issue->hasEnabledSubIssues()) {
            return [];
        }

        return $this->featureIssueQuery($issue)->getQuery()->getResult();
    }

    public function issueQuery(Project $project, QueryParams $params): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('issue');

        $queryBuilder
            ->join('issue.type', 'type')
            ->join('issue.issueColumn', 'column')
            ->join('issue.createdBy', 'createdBy')
            ->where('issue.project = :project')
            ->sqidParameter('project', $project->getId());

        $this->applyIssueFilters($queryBuilder, $params);

        return $queryBuilder;
    }

    public function getColumnLastOrder(Project $project, IssueColumn $issueColumn): int
    {
        $query = $this->columnQuery($project, $issueColumn);

        $query->select('max(issue.columnOrder) as columnOrder');

        return $query->getQuery()->getSingleScalarResult() ?? Issue::DEFAULT_ORDER_SPACE;
    }

    public function getSubIssueFirstOrder(Issue $issue): int
    {
        $query = $this->createQueryBuilder('issue')
            ->andWhere('issue.parent = :parent')
            ->sqidParameter('parent', $issue->getId());

        $query->select('min(issue.issueOrder)');

        return $query->getQuery()->getSingleScalarResult() ?? Issue::DEFAULT_ORDER_SPACE;
    }

    /**
     * @param int[] $issueIds
     * @return array<int, Issue>
     */
    public function mappedIssues(array $issueIds): array
    {
        $issues = $this->findBy([
            'id' => $issueIds
        ]);

        return ArrayHelper::indexByCallback($issues, fn(Issue $issue) => $issue->getId()->integerId());
    }

    public function reorderFeature(Issue $issue): void
    {
        $queryBuilder = $this->createQueryBuilder('issue');

        $query = $queryBuilder
            ->where('issue.parent = :parent')
            ->sqidParameter('parent', $issue->getId())
            ->andWhere('issue.type = :type')
            ->setParameter('type', $this->issueTypeRepository->subIssueType())
            ->getQuery();


        $batchSize = 20;
        $i = 1;

        /**
         * @var Issue $issue
         */
        foreach ($query->toIterable() as $issue) {
            $issue->setIssueOrder($i * Issue::DEFAULT_ORDER_SPACE);

            if (($i % $batchSize) === 0) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear();
            }
            $i++;
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param Issue $issue
     * @param string $search
     * @return Issue[]
     */
    public function searchIssueDependencies(Issue $issue, string $search): array
    {
        $excludedIds = array_merge($issue->getIssueDependencies()
            ->map(fn(IssueDependency $issueDependency) => $issueDependency->getDependency()->getId()->integerId())
            ->getValues(), [$issue->getId()->integerId()]);

        $queryBuilder = $this->createQueryBuilder('issue');

        $queryBuilder
            ->notIn('issue.id', $excludedIds)
            ->fuzzyLike("CONCAT(issue.title, ' #', issue.number)", $search)
            ->setMaxResults(10);

        return $queryBuilder->getQuery()->getResult();
    }

    private function applyIssueFilters(QueryBuilder $queryBuilder, QueryParams $params): void
    {
        if (!$params->getFilters()) {
            return;
        }

        /**
         * @var IssueSearchForm $filters
         */
        $filters = $params->getFilters();

        foreach ($this->issueFilters() as $filterName => $filter) {
            if ($filters->$filterName) {
                $filter($queryBuilder, $filters->$filterName);
            }
        }
    }

    private function issueFilters(): array
    {
        return [
            'title' => function (QueryBuilder $queryBuilder, string $value) {
                $queryBuilder->andWhere('issue.title LIKE LOWER(:title)');
                $queryBuilder->searchParameter('title', $value);
            },
            'number' => function (QueryBuilder $queryBuilder, int $value) {
                $queryBuilder->andWhere('issue.number = :number');
                $queryBuilder->setParameter('number', $value);
            },
            'type' => function (QueryBuilder $queryBuilder, IssueType $value) {
                $queryBuilder->andWhere('issue.type = :type');
                $queryBuilder->setParameter('type', $value);
            },
            'column' => function (QueryBuilder $queryBuilder, IssueColumn $value) {
                $queryBuilder->andWhere('issue.issueColumn = :column');
                $queryBuilder->setParameter('column', $value);
            },
            'createdBy' => function (QueryBuilder $queryBuilder, User $value) {
                $queryBuilder->andWhere('issue.createdBy = :user');
                $queryBuilder->setParameter('user', $value);
            },
            'createdAfter' => function (QueryBuilder $queryBuilder, DateTimeImmutable $value) {
                $queryBuilder->andWhere('issue.createdAt >= :createdAfter');
                $queryBuilder->setParameter('createdAfter', $value);
            },
            'createdBefore' => function (QueryBuilder $queryBuilder, DateTimeImmutable $value) {
                $queryBuilder->andWhere('issue.createdAt <= :createdBefore');
                $queryBuilder->setParameter('createdBefore', $value);
            },
            'updatedAfter' => function (QueryBuilder $queryBuilder, DateTimeImmutable $value) {
                $queryBuilder->andWhere('issue.updatedAt >= :updatedAfter');
                $queryBuilder->setParameter('updatedAfter', $value);
            },
            'updatedBefore' => function (QueryBuilder $queryBuilder, DateTimeImmutable $value) {
                $queryBuilder->andWhere('issue.updatedAt <= :updatedBefore');
                $queryBuilder->setParameter('updatedBefore', $value);
            },
        ];
    }

    /**
     * @param Issue $positionable
     * @return void
     */
    public function reorder($positionable): void
    {
        $query = $this->orderedColumnQuery($positionable->getProject(), $positionable->getIssueColumn())->getQuery();

        $batchSize = 20;
        $i = 1;
        /**
         * @var Issue $issue
         */
        foreach ($query->toIterable() as $issue) {
            $issue->setColumnOrder($i * Issue::DEFAULT_ORDER_SPACE);

            if (($i % $batchSize) === 0) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear();
            }
            $i++;
        }

        $this->getEntityManager()->flush();
    }
}
