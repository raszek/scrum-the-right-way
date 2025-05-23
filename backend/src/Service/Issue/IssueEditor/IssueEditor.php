<?php

namespace App\Service\Issue\IssueEditor;

use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Enum\Issue\IssueColumnEnum;
use App\Event\Issue\Event\SetIssueDescriptionEvent;
use App\Event\Issue\Event\SetIssueStoryPointsEvent;
use App\Exception\Issue\CannotSetIssueTitleException;
use App\Exception\Issue\CannotSetStoryPointsException;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Helper\JsonHelper;
use App\Helper\StringHelper;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use App\Service\Issue\FeatureEditorFactory;
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
        private EventPersister $eventPersister,
        private FeatureEditorFactory $featureEditorFactory
    ) {
    }

    public function changeKanbanColumn(IssueColumnEnum $column, int $position): void
    {
        if ($this->issue->getIssueColumn()->getId() === $column->value) {
            return;
        }

        if ($this->issue->isFeature()) {
            throw new RuntimeException('Cannot move features on kanban.');
        }

        if (!in_array($column, IssueColumnEnum::kanbanColumns())) {
            throw new RuntimeException('Invalid column. This method can only change columns in kanban.');
        }

        $this->projectIssueEditorStrategy->changeKanbanColumn($column);

        $this->issue->setIssueColumn($this->issueColumnRepository->fromEnum($column));
        $this->updateParentFeature();
        $this->entityManager->flush();

        $this->sort($position);
        $this->moveBackInProgressIssue($column);
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
        $query->setParameter('issueId', $this->issue->getId()->integerId());

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

        $this->guardAgainstNonEditableIssue();

        $this->issue->setTitle($title);
        $this->issue->setUpdatedAt($this->clock->now());

        $this->entityManager->flush();
    }

    public function updateDescription(?string $description): void
    {
        $this->guardAgainstNonEditableIssue();

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
            issueId: $this->issue->getId()->integerId(),
            historyId: $change->getId()->integerId()
        ), $this->issue);
    }

    public function getIssueEditableError(): ?string
    {
        return $this->projectIssueEditorStrategy->getIssueEditableError();
    }

    public function isIssueEditable(): bool
    {
        return $this->getIssueEditableError() === null;
    }

    public function setStoryPoints(?int $storyPoints): void
    {
        if ($storyPoints !== null && $storyPoints <= 0) {
            throw new CannotSetStoryPointsException('Story points value must be bigger than 0');
        }

        if ($this->issue->isFeature()) {
            throw new RuntimeException('Cannot change story points for feature.');
        }

        $this->guardAgainstNonEditableIssue();

        $this->issue->setStoryPoints($storyPoints);
        $this->issue->setUpdatedAt($this->clock->now());

        if ($this->issue->isSubIssue()) {
            $this->updateFeatureStoryPoints($this->issue->getParent());
        }

        $this->entityManager->flush();

        $event = new SetIssueStoryPointsEvent(
            issueId: $this->issue->getId()->integerId(),
            storyPoints: $storyPoints
        );

        $this->eventPersister->createIssueEvent($event, $this->issue);
    }

    public function archive(): void
    {
        $this->issue->setIssueColumn($this->issueColumnRepository->archivedColumn());

        $this->entityManager->flush();
    }

    public function moveBackInProgressIssue(IssueColumnEnum $column): void
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

        $this->entityManager->flush();
    }

    private function updateParentFeature(): void
    {
        if (!$this->issue->isSubIssue()) {
            return;
        }

        $featureEditor = $this->featureEditorFactory->create($this->issue->getParent(), $this->user);

        $featureEditor->updateIssueColumn();
    }

    private function updateFeatureStoryPoints(Issue $feature): void
    {
        $featureStoryPoints = $feature->getSubIssues()
            ->filter(fn(Issue $issue) => $issue->getStoryPoints() !== null)
            ->map(fn(Issue $issue) => $issue->getStoryPoints())
            ->toArray();

        $feature->setStoryPoints(array_sum($featureStoryPoints));
    }

    private function guardAgainstNonEditableIssue(): void
    {
        $issueEditableError = $this->getIssueEditableError();
        if ($issueEditableError) {
            throw new RuntimeException($issueEditableError);
        }
    }
}
