<?php

namespace App\Service\Tag;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Repository\Project\ProjectTagRepository;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueTagEditorFactory
{

    public function __construct(
        private ProjectTagRepository $projectTagRepository,
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    public function create(Issue $issue, User $user): IssueTagEditor
    {
        return new IssueTagEditor(
            issue: $issue,
            projectTagRepository: $this->projectTagRepository,
            entityManager: $this->entityManager,
            eventPersister: $this->eventPersisterFactory->create($issue->getProject(), $user)
        );
    }

}
