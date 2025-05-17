<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Repository\Issue\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SubIssueEditorFactory
{

    public function __construct(
        private IssueRepository $issueRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(Issue $issue): SubIssueEditor
    {
        return new SubIssueEditor(
            issue: $issue,
            issueRepository: $this->issueRepository,
            entityManager: $this->entityManager,
        );
    }

}
