<?php

namespace App\Repository\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueDependency;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueDependency>
 */
class IssueDependencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueDependency::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    /**
     * @param Issue $issue
     * @return IssueDependency[]
     */
    public function issueDependencies(Issue $issue): array
    {
        $queryBuilder = $this->createQueryBuilder('issueDependency');

        $queryBuilder
            ->where('issueDependency.issue = :issue')
            ->setParameter('issue', $issue->getId()->integerId());

        return $queryBuilder->getQuery()->getResult();
    }
}
