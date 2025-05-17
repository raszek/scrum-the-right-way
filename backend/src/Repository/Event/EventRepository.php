<?php

namespace App\Repository\Event;

use App\Entity\Event\Event;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Form\Event\SearchEventForm;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    /**
     * @param Issue $issue
     * @return Event[]
     */
    public function issueEvents(Issue $issue): array
    {
        return $this->findBy([
            'issue' => $issue
        ]);
    }

    public function listQuery(Project $project, SearchEventForm $searchEventForm): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('event');

        $queryBuilder
            ->addSelect('user')
            ->leftJoin('event.createdBy', 'user')
            ->where('event.project = :project')
            ->sqidParameter('project', $project->getId())
            ->orderBy('event.createdAt', 'desc');

        if ($searchEventForm->name) {
            $queryBuilder->andWhere('event.name = :name')
                ->setParameter('name', $searchEventForm->name);
        }
        
        if ($searchEventForm->createdBy) {
            $queryBuilder->andWhere('event.createdBy = :user')
                ->setParameter('user', $searchEventForm->createdBy);
        }

        return $queryBuilder;
    }
}
