<?php

namespace App\Service\Sprint;

use App\Entity\Sprint\Sprint;
use App\Repository\Issue\IssueColumnRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private IssueColumnRepository $issueColumnRepository,
    ) {
    }

    public function create(Sprint $sprint): SprintEditor
    {
        return new SprintEditor(
            sprint: $sprint,
            entityManager: $this->entityManager,
            issueColumnRepository: $this->issueColumnRepository,
        );
    }
}
