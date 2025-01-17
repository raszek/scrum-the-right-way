<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use Doctrine\ORM\EntityManagerInterface;

readonly class DependencyIssueEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(Issue $issue): DependencyIssueEditor
    {
        return new DependencyIssueEditor(
            issue: $issue,
            entityManager: $this->entityManager
        );
    }
}
