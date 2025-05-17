<?php

namespace App\Service\Issue;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersisterFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectIssueEditorFactory
{

    public function __construct(
        private IssueRepository $issueRepository,
        private ClockInterface $clock,
        private IssueColumnRepository $issueColumnRepository,
        private EntityManagerInterface $entityManager,
        private EventPersisterFactory $eventPersisterFactory
    ) {
    }

    public function create(Project $project, ProjectMember $projectMember): ProjectIssueEditor
    {
        return new ProjectIssueEditor(
            project: $project,
            member: $projectMember,
            issueRepository: $this->issueRepository,
            issueColumnRepository: $this->issueColumnRepository,
            clock: $this->clock,
            entityManager: $this->entityManager,
            eventPersister: $this->eventPersisterFactory->create($project, $projectMember->getUser())
        );
    }

}
