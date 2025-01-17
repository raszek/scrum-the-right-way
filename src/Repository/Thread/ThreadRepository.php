<?php

namespace App\Repository\Thread;

use App\Entity\Project\Project;
use App\Entity\Thread\Thread;
use App\Form\Thread\SearchThreadForm;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Thread>
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    /**
     * @param int[] $threadIds
     * @return Thread[]
     */
    public function findInIds(array $threadIds): array
    {
        $queryBuilder = $this->createQueryBuilder('thread');

        $queryBuilder
            ->where('thread.id in (:ids)')
            ->setParameter('ids', $threadIds)
            ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function threadsQuery(Project $project, SearchThreadForm $searchThreadForm): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('thread');

        $queryBuilder
            ->where('thread.project = :project')
            ->sqidParameter('project', $project->getId())
            ->join('thread.createdBy', 'createdBy')
            ->join('thread.status', 'status')
            ->orderBy('thread.status')
            ->addOrderBy('thread.updatedAt', 'desc')
            ;

        if ($searchThreadForm->title) {
            $queryBuilder->andWhere('LOWER(thread.title) like :title')
                ->searchParameter('title', $searchThreadForm->title);
        }

        if ($searchThreadForm->status) {
            $queryBuilder->andWhere('status.id = :status')
                ->setParameter('status', $searchThreadForm->status->value);
        }

        return $queryBuilder;
    }
}
