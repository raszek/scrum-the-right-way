<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueColumn;
use App\Entity\User\User;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Issue\IssueTypeRepository;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class SubIssueEditor
{

    public function __construct(
        private Issue $issue,
        private User $user,
        private EntityManagerInterface $entityManager,
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
        private IssueTypeRepository $issueTypeRepository,
        private ClockInterface $clock,
    ) {
    }

    public function add(SubIssueForm $subIssueForm): void
    {
        if (!$this->issue->isFeature()) {
            throw new RuntimeException('Non feature issue cannot have sub issues.');
        }

        $nextIssueNumber = $this->issueRepository->getNextIssueNumber($this->issue->getProject());

        $subIssueColumn = $this->getSubIssueColumn();

        $lastColumnOrder = $this->issueRepository->getColumnLastOrder($this->issue->getProject(), $subIssueColumn);

        $subIssue = new Issue(
            number: $nextIssueNumber,
            title: $subIssueForm->title,
            columnOrder: $lastColumnOrder,
            issueColumn: $subIssueColumn,
            type: $this->issueTypeRepository->subIssueType(),
            project: $this->issue->getProject(),
            createdBy: $this->user,
            createdAt: $this->clock->now(),
        );

        $subIssue->setParent($this->issue);

        $this->entityManager->persist($subIssue);

        $this->entityManager->flush();
    }

    private function getSubIssueColumn(): IssueColumn
    {
        if ($this->issue->getIssueColumn()->isBacklog()) {
            return $this->issueColumnRepository->backlogColumn();
        }

        return $this->issueColumnRepository->toDoColumn();
    }
}
