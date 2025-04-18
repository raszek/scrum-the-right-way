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

    public function fromEnum(IssueColumnEnum $enum): IssueColumn
    {
        return $this->getReference($enum->value);
    }

    public function inProgressColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::InProgress->value);
    }

    public function backlogColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::Backlog->value);
    }

    public function testColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::Test->value);
    }

    public function inTestsColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::InTests->value);
    }

    public function toDoColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::ToDo->value);
    }

    public function archivedColumn(): IssueColumn
    {
        return $this->getReference(IssueColumnEnum::Archived->value);
    }

    private function getReference(int $id): IssueColumn
    {
        return $this->getEntityManager()->getReference(IssueColumn::class, $id);
    }
}
