<?php

namespace App\Service\Issue\IssueEditor;

use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Enum\Issue\IssueColumnEnum;
use App\Event\Issue\Event\SetIssueDescriptionEvent;
use App\Event\Issue\Event\SetIssueStoryPointsEvent;
use App\Exception\Issue\CannotSetIssueDescriptionException;
use App\Exception\Issue\CannotSetIssueTitleException;
use App\Exception\Issue\CannotSetStoryPointsException;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Helper\JsonHelper;
use App\Helper\StringHelper;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use App\Service\Position\Positioner;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\DiffHelper;
use RuntimeException;
use Symfony\Component\String\UnicodeString;

readonly class IssueEditor
{

    public function __construct(
        private Issue $issue,
        private User $user,
        private IssueRepository $issueRepository,
        private IssueColumnRepository $issueColumnRepository,
        private ProjectIssueEditorStrategy $projectIssueEditorStrategy,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private EventPersister $eventPersister
    ) {
    }

    public function changeKanbanColumn(IssueColumnEnum $column): void
    {
        if ($this->issue->getIssueColumn()->getId() === $column->value) {
            return;
        }

        if (!in_array($column, IssueColumnEnum::kanbanColumns())) {
            throw new RuntimeException('Invalid column. This method can only change columns in kanban.');
        }

        $this->projectIssueEditorStrategy->changeKanbanColumn($column);

        $this->setInProgressIssue($column);

        $this->issue->setIssueColumn($this->issueColumnRepository->fromEnum($column));

        $this->entityManager->flush();
    }

    /**
     * @param int $position
     * @return void
     * @throws OutOfBoundPositionException
     */
    public function sort(int $position): void
    {
        $query = $this->issueRepository->orderedColumnQuery($this->issue->getProject(), $this->issue->getIssueColumn());
        $query->andWhere('issue.id <> :issueId');
        $query->setParameter('issueId', $this->issue->getId());

        $positioner = new Positioner(
            query: $query,
            positioned: $this->issue,
            reorderService: $this->issueRepository
        );

        $positioner->setPosition($position);

        $this->entityManager->flush();
    }

    public function updateTitle(string $title): void
    {
        $unicodeTitle = new UnicodeString($title);

        if ($unicodeTitle->length() > Issue::TITLE_LENGTH) {
            throw new CannotSetIssueTitleException(
                sprintf('Issue title cannot be longer than %d characters', Issue::TITLE_LENGTH)
            );
        }

        if (!$this->issue->isOnBacklogColumn()) {
            throw new CannotSetIssueDescriptionException('Cannot change title outside of backlog');
        }

        $this->issue->setTitle($title);
        $this->issue->setUpdatedAt($this->clock->now());

        $this->entityManager->flush();
    }

    public function updateDescription(?string $description): void
    {
        if (!$this->issue->isOnBacklogColumn()) {
            throw new CannotSetIssueDescriptionException('Cannot change description outside of backlog');
        }

        $oldDescription = $this->issue->getDescription()
            ? StringHelper::explodeNewLine($this->issue->getDescription())
            : '';

        $this->issue->setDescription($description);
        $this->issue->setUpdatedAt($this->clock->now());


        $newChanges = StringHelper::explodeNewLine($description);

        $jsonResult = DiffHelper::calculate($oldDescription, $newChanges, 'Json');

        $change = new DescriptionHistory(
            issue: $this->issue,
            changes: JsonHelper::decode($jsonResult),
            createdAt: $this->clock->now()
        );

        $this->entityManager->persist($change);

        $this->entityManager->flush();

        $this->eventPersister->createIssueEvent(new SetIssueDescriptionEvent(
            issueId: $this->issue->getId(),
            historyId: $change->getId()->integerId()
        ), $this->issue);
    }

    public function setStoryPoints(?int $storyPoints): void
    {
        if ($storyPoints !== null && $storyPoints <= 0) {
            throw new CannotSetStoryPointsException('Story points value must be bigger than 0');
        }

        if (!$this->issue->isOnBacklogColumn()) {
            throw new CannotSetStoryPointsException('Cannot change story points outside of backlog');
        }

        $this->issue->setStoryPoints($storyPoints);
        $this->issue->setUpdatedAt($this->clock->now());

        $this->entityManager->flush();

        $event = new SetIssueStoryPointsEvent(
            issueId: $this->issue->getId(),
            storyPoints: $storyPoints
        );

        $this->eventPersister->createIssueEvent($event, $this->issue);
    }

    public function archive(): void
    {
        $this->issue->setIssueColumn($this->issueColumnRepository->archivedColumn());

        $this->entityManager->flush();
    }

    private function setInProgressIssue(IssueColumnEnum $column): void
    {
        $inProgressIssue = $this->user->getInProgressIssue();
        if (!$inProgressIssue) {
            if ($column->isInProgress() || $column->isInTests()) {
                $this->user->setInProgressIssue($this->issue);
            }
            return;
        }

        if ($column->isInProgress()) {
            $inProgressIssue->setIssueColumn($this->issueColumnRepository->toDoColumn());

            $this->user->setInProgressIssue($this->issue);
        } else if ($column->isInTests()) {
            $inProgressIssue->setIssueColumn($this->issueColumnRepository->testColumn());

            $this->user->setInProgressIssue($this->issue);
        } else {
            $this->user->setInProgressIssue(null);
        }

    }
}
