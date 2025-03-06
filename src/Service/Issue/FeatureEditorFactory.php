<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Issue\IssueTypeRepository;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class FeatureEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
        private IssueTypeRepository $issueTypeRepository,
        private ClockInterface $clock
    ) {
    }

    public function create(Issue $issue, User $user): FeatureEditor
    {
        return new FeatureEditor(
            issue: $issue,
            user: $user,
            entityManager: $this->entityManager,
            issueRepository:  $this->issueRepository,
            issueColumnRepository: $this->issueColumnRepository,
            issueTypeRepository: $this->issueTypeRepository,
            clock: $this->clock,
        );
    }

}
