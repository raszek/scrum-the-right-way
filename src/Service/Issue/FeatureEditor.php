<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueColumn;
use App\Entity\User\User;
use App\Enum\Issue\IssueColumnEnum;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Issue\IssueTypeRepository;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class FeatureEditor
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
        $this->validateService();
    }

    private function validateService(): void
    {
        if (!$this->issue->isFeature()) {
            throw new RuntimeException('This class can only edit feature issues.');
        }
    }

    public function add(SubIssueForm $subIssueForm): Issue
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
            parent: $this->issue,
            issueOrder: $this->getIssueOrder()
        );

        $this->entityManager->persist($subIssue);

        $this->entityManager->flush();

        return $subIssue;
    }

    public function updateIssueColumn(): void
    {
        $column = match(true) {
            $this->isEverySubIssueHasColumn(IssueColumnEnum::ToDo) => $this->issueColumnRepository->toDoColumn(),
            $this->isInProgress() => $this->issueColumnRepository->inProgressColumn(),
            $this->isEverySubIssueHasColumn(IssueColumnEnum::Test) => $this->issueColumnRepository->testColumn(),
            $this->isInTests() => $this->issueColumnRepository->inTestsColumn(),
            default => $this->getMinimumColumn()
        };

        $this->issue->setIssueColumn($column);
    }

    private function getMinimumColumn(): IssueColumn
    {
        $columnIds = $this->issue->getSubIssues()
            ->map(fn(Issue $subIssue) => $subIssue->getIssueColumn()->getId())
            ->toArray();

        $minimumColumnId = min($columnIds);

        return $this->issueColumnRepository->fromEnum(IssueColumnEnum::tryFrom($minimumColumnId));
    }

    private function isInTests(): bool
    {
        return $this->hasColumnSubIssue(IssueColumnEnum::Test) || $this->hasColumnSubIssue(IssueColumnEnum::Test);
    }

    private function isInProgress(): bool
    {
        return $this->hasToDoSubIssue() || $this->hasInProgressIssue();
    }

    private function hasInProgressIssue(): bool
    {
        return $this->hasColumnSubIssue(IssueColumnEnum::InProgress);
    }

    private function hasToDoSubIssue(): bool
    {
        return $this->hasColumnSubIssue(IssueColumnEnum::ToDo);
    }

    private function isEverySubIssueHasColumn(IssueColumnEnum $columnEnum): bool
    {
        $columnCount = $this->issue->getSubIssues()
            ->filter(fn(Issue $subIssue) => $subIssue->isOnColumn($columnEnum))
            ->count();

        return $this->issue->getSubIssues()->count() === $columnCount;
    }

    private function hasColumnSubIssue(IssueColumnEnum $columnEnum): bool
    {
        foreach ($this->issue->getSubIssues() as $subIssue) {
            if ($subIssue->isOnColumn($columnEnum)){
                return true;
            }
        }

        return false;
    }

    /**
     * Sub issue first equals 1 than it means there is no space to put sub issue at first position
     * and we have to reorder whole feature to find place
     * @return int
     */
    private function getIssueOrder(): int
    {
        $subIssueFirstOrder = $this->issueRepository->getSubIssueFirstOrder($this->issue);

        if ($subIssueFirstOrder <= 1) {
            $this->issueRepository->reorderFeature($this->issue);

            return floor(Issue::DEFAULT_ORDER_SPACE / 2);
        }

        return floor($subIssueFirstOrder / 2);
    }

    private function getSubIssueColumn(): IssueColumn
    {
        if ($this->issue->getIssueColumn()->isBacklog()) {
            return $this->issueColumnRepository->backlogColumn();
        }

        return $this->issueColumnRepository->toDoColumn();
    }
}
