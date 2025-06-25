<?php

namespace App\Repository\Room;

use App\Entity\Project\Project;
use App\Entity\Room\RoomIssue;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoomIssue>
 */
class RoomIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomIssue::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return new QueryBuilder($this->getEntityManager())
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function findRoomIssue(string $issueId, string $roomId, Project $project): ?RoomIssue
    {
        $query = $this->createQueryBuilder('roomIssue');
        $query
            ->join('roomIssue.room', 'room')
            ->where('roomIssue.issue = :issue')
            ->andWhere('roomIssue.room = :room')
            ->andWhere('room.project = :project')
            ->sqidParameter('issue', $issueId)
            ->sqidParameter('room', $roomId)
            ->sqidParameter('project', $project->getId());

        return $query->getQuery()->getOneOrNullResult();
    }
}
