<?php

namespace App\Repository\Issue;

use App\Entity\Issue\IssueColumn;
use App\Enum\Issue\IssueColumnEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueColumn>
 */
class IssueColumnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueColumn::class);
    }

    public function backlogColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::Backlog->value);
    }

    public function toDoColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::ToDo->value);
    }

    private function getReference(int $id): IssueColumn
    {
        return $this->getEntityManager()->getReference(IssueColumn::class, $id);
    }
}
