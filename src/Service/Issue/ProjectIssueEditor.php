<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueColumn;
use App\Entity\Issue\IssueObserver;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Event\Issue\Event\CreateIssueEvent;
use App\Form\Issue\CreateIssueForm;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProjectIssueEditor
{

    public function __construct(
        private Project $project,
        private ProjectMember $member,
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager,
        private EventPersister $eventPersister
    ) {
    }

    public function createIssue(CreateIssueForm $form): void
    {
        $nextIssueNumber = $this->issueRepository->getNextIssueNumber($this->project);

        $backlogColumn = $this->issueColumnRepository->backlogColumn();

        $newIssueColumnOrder = $this->getNewIssueColumnOrder($backlogColumn);

        $createdIssue = new Issue(
            number: $nextIssueNumber,
            title: $form->title,
            columnOrder: $newIssueColumnOrder,
            issueColumn: $backlogColumn,
            type: $form->type,
            project: $this->project,
            createdBy: $this->member->getUser(),
            createdAt: $this->clock->now()
        );

        $this->entityManager->persist($createdIssue);

        $observer = new IssueObserver(
            issue: $createdIssue,
            projectMember: $this->member
        );

        $this->entityManager->persist($observer);

        $this->entityManager->flush();

        $this->eventPersister->createIssueEvent(new CreateIssueEvent(
            issueId: $createdIssue->getId(),
        ), $createdIssue);
    }

    private function getNewIssueColumnOrder(IssueColumn $column): int
    {
        $query = $this->issueRepository->columnQuery($this->project, $column);

        $query->setMaxResults(1);

        $issues = $query->getQuery()->getResult();

        if (count($issues) === 0) {
            return Issue::DEFAULT_ORDER_SPACE;
        }

        /**
         * @var Issue $firstIssue
         */
        $firstIssue = $issues[0];

        if ($firstIssue->getColumnOrder() === 1) {
            $this->issueRepository->reorderColumn($this->project, $this->issueColumnRepository->backlogColumn());

            return Issue::DEFAULT_ORDER_SPACE / 2;
        }

        return round($firstIssue->getColumnOrder() / 2);
    }

}
