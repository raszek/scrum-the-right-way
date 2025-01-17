<?php

namespace App\Repository\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueThreadMessage;
use App\Entity\Thread\ThreadMessage;
use App\Helper\ArrayHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueThreadMessage>
 */
class IssueThreadMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueThreadMessage::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    /**
     * @param Issue $issue
     * @return ThreadMessage[]
     */
    public function getIssueMessages(Issue $issue): array
    {
        $issueMessages = $this->findBy(['issue' => $issue]);

        return ArrayHelper::map(
            $issueMessages,
            fn(IssueThreadMessage $issueMessage) => $issueMessage->getThreadMessage()
        );
    }
}
