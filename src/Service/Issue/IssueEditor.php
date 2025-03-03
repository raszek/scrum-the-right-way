<?php

namespace App\Service\Issue;

use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Event\Issue\Event\SetIssueDescriptionEvent;
use App\Event\Issue\Event\SetIssueStoryPointsEvent;
use App\Exception\Issue\CannotSetIssueDescriptionException;
use App\Exception\Issue\CannotSetIssueTitleException;
use App\Exception\Issue\CannotSetStoryPointsException;
use App\Exception\Issue\NoOrderSpaceException;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Helper\JsonHelper;
use App\Helper\StringHelper;
use App\Repository\Issue\IssueRepository;
use App\Service\Common\ClockInterface;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\DiffHelper;
use Symfony\Component\String\UnicodeString;

readonly class IssueEditor
{

    public function __construct(
        private Issue $issue,
        private IssueRepository $issueRepository,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private EventPersister $eventPersister
    ) {
    }

    /**
     * @param int $position
     * @return void
     * @throws OutOfBoundPositionException
     */
    public function setPosition(int $position): void
    {
        $query = $this->issueRepository->orderedColumnQuery($this->issue->getProject(), $this->issue->getIssueColumn());
        $query->andWhere('issue.id <> :issueId');
        $query->setParameter('issueId', $this->issue->getId());

        $isFirstPosition = $position <= 1;
        if ($isFirstPosition) {
            $query->setMaxResults(1);
        } else {
            $query->setFirstResult($position - 2);
            $query->setMaxResults(2);
        }

        $issues = $query->getQuery()->getResult();

        try {
            $order = $this->calculateOrder($issues, $isFirstPosition);
            $this->issue->setColumnOrder($order);
        } catch (NoOrderSpaceException) {
            $this->issueRepository->reorderColumn($this->issue->getProject(), $this->issue->getIssueColumn());
            $this->setPosition($position);
        }

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


    /**
     * @param Issue[] $issues
     * @param bool $isFirstPosition
     * @return int
     * @throws NoOrderSpaceException
     * @throws OutOfBoundPositionException
     */
    private function calculateOrder(array $issues, bool $isFirstPosition): int
    {
        if (count($issues) === 0) {
            throw new OutOfBoundPositionException('Position number is bigger than issue count in the column');
        }

        if (count($issues) === 1) {
            if ($isFirstPosition) {
                return $this->findOrderBetween(0, $issues[0]->getColumnOrder());
            } else {
                return $issues[0]->getColumnOrder() + Issue::DEFAULT_ORDER_SPACE;
            }
        }

        return $this->findOrderBetween($issues[0]->getColumnOrder(), $issues[1]->getColumnOrder());
    }

    private function findOrderBetween(int $firstOrder, int $secondOrder): int
    {
        if (abs($firstOrder - $secondOrder) <= 1) {
            throw new NoOrderSpaceException('No order space exception');
        }

        return ($firstOrder + $secondOrder) / 2;
    }

}
