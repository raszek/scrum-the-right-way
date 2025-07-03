<?php

namespace App\Repository\Room;

use App\Doctrine\Sqid;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Room\RoomIssue;
use App\Helper\ArrayHelper;
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

    /**
     * @param string $roomId
     * @return int[]
     */
    public function findRoomIssueIds(string $roomId): array
    {
        $query = $this->createQueryBuilder('roomIssue');

        $query
            ->select(['issue.id as id'])
            ->join('roomIssue.issue', 'issue')
            ->where('roomIssue.room = :room')
            ->sqidParameter('room', $roomId);

        $ids = array_column($query->getQuery()->getArrayResult(), 'id');

        return ArrayHelper::map($ids, fn(Sqid $id) => $id->integerId());
    }

    public function findByIssueId(string $issueId, string $roomId): ?RoomIssue
    {
        $query = $this->createQueryBuilder('roomIssue');

        $query->where('roomIssue.issue = :issue');
        $query->sqidParameter('issue', $issueId);
        $query->andWhere('roomIssue.room = :room');
        $query->sqidParameter('room', $roomId);

        return $query->getQuery()->getOneOrNullResult();
    }
}
