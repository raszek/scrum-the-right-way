<?php

namespace App\Repository\Issue;

use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Helper\ArrayHelper;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DescriptionHistory>
 */
class DescriptionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DescriptionHistory::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function itemsQuery(Issue $issue): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('descriptionHistory');

        $queryBuilder->where('descriptionHistory.issue = :issue');
        $queryBuilder->setParameter('issue', $issue);

        $queryBuilder->orderBy('descriptionHistory.createdAt', 'DESC');

        return $queryBuilder;
    }

    public function mappedHistories(array $historyIds): array
    {
        $queryBuilder = $this->createQueryBuilder('descriptionHistory');

        $queryBuilder->where('descriptionHistory.id in (:historyIds)');
        $queryBuilder->setParameter('historyIds', $historyIds);

        $records = $queryBuilder->getQuery()->getResult();

        return ArrayHelper::indexByCallback($records, fn(DescriptionHistory $history) => $history->getId()->integerId());
    }
}
