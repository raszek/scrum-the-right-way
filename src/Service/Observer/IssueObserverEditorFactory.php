<?php

namespace App\Service\Observer;

use App\Entity\Issue\Issue;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueObserverEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(Issue $issue): IssueObserverEditor
    {
        return new IssueObserverEditor(
            issue: $issue,
            entityManager: $this->entityManager
        );
    }
}
