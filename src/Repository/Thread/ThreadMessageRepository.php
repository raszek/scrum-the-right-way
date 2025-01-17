<?php

namespace App\Repository\Thread;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueThreadMessage;
use App\Entity\Thread\Thread;
use App\Entity\Thread\ThreadMessage;
use App\Helper\ArrayHelper;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThreadMessage>
 */
class ThreadMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadMessage::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    /**
     * @param int[] $ids
     * @return array<int, ThreadMessage>
     */
    public function mappedMessages(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('threadMessage');

        $queryBuilder->in('threadMessage.id', $ids);

        $threadMessages = $queryBuilder->getQuery()->getResult();

        return ArrayHelper::indexByCallback(
            $threadMessages,
            fn(ThreadMessage $threadMessage) => $threadMessage->getId()->integerId()
        );
    }

    /**
     * @param Thread[] $threads
     * @return array
     */
    public function threadMessageCounts(array $threads): array
    {
        $threadIds = ArrayHelper::map($threads, fn(Thread $thread) => $thread->getId()->integerId());

        $query = $this->createQueryBuilder('threadMessage');

        $query
            ->select([
                'thread.id as thread_id',
                'count(threadMessage.id) as message_count',
            ])
            ->join('threadMessage.thread', 'thread')
            ->where('threadMessage.thread in (:threadIds)')
            ->setParameter('threadIds', $threadIds)
            ->groupBy('thread.id');
        
        $records = $query->getQuery()->getArrayResult();

        return ArrayHelper::map($records, fn($record) => [
            'thread_id' => $record['thread_id']->integerId(),
            'message_count' => $record['message_count'],
        ]);
    }

    /**
     * @param Thread $thread
     * @return ThreadMessage[]
     */
    public function getThreadMessages(Thread $thread): array
    {
        $query = $this->createQueryBuilder('threadMessage');

        $query
            ->where('threadMessage.thread = :thread')
            ->sqidParameter('thread', $thread->getId())
            ->orderBy('threadMessage.createdAt');

        return $query->getQuery()->getResult();
    }

    /**
     * @param string $search
     * @param Issue $issue
     * @param int $limit
     * @return ThreadMessage[]
     */
    public function searchIssueMessages(string $search, Issue $issue, int $limit = 10): array
    {
        $query = $this->createQueryBuilder('threadMessage');

        $currentMessageIds = $issue->getIssueThreadMessages()
            ->map(fn(IssueThreadMessage $issueMessage) => $issueMessage->getThreadMessage()->getId()->integerId())
            ->toArray();

        $query
            ->addSelect('thread')
            ->join('threadMessage.thread', 'thread')
            ->where('thread.project = :project')
            ->sqidParameter('project', $issue->getProject()->getId())
            ->notIn('threadMessage.id', $currentMessageIds)
            ->setMaxResults($limit);

        $this->fuzzySearch($query, $search);

        return $query->getQuery()->getResult();
    }

    public function getMessageNextNumber(Thread $thread): int
    {
        $query = $this->createQueryBuilder('threadMessage');

        $query
            ->select('max(threadMessage.number)')
            ->where('threadMessage.thread = :thread')
            ->sqidParameter('thread', $thread->getId());

        return $query->getQuery()->getSingleScalarResult() + 1;
    }

    private function fuzzySearch(QueryBuilder $queryBuilder, string $search): void
    {
        $parts = explode(' ', $search);

        foreach ($parts as $i => $part) {
            $queryBuilder
                ->andWhere("CONCAT(LOWER(thread.title), ' #', threadMessage.number) LIKE :s".$i)
                ->searchParameter('s'.$i, $part);
        }
    }
}
