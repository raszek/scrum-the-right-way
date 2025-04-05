<?php

namespace App\Service\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Exception\Sprint\CannotStartSprintException;
use App\Form\Sprint\CreateSprintGoalForm;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class SprintEditor
{

    public function __construct(
        private Sprint $sprint,
        private EntityManagerInterface $entityManager,
        private IssueColumnRepository $issueColumnRepository,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
        private SprintGoalRepository $sprintGoalRepository,
        private ClockInterface $clock,
    ) {
    }

    public function addSprintIssue(Issue $issue): void
    {
        if ($issue->getProject()->getId() !== $this->sprint->getProject()->getId()) {
            throw new RuntimeException('Issue is from other project');
        }

        if ($issue->isSubIssue()) {
            throw new RuntimeException('Cannot add sub issue to sprint');
        }

        if (!$this->sprint->isCurrent()) {
            throw new RuntimeException('Issue can be added only to current sprint');
        }

        $firstSprintGoal = $this->sprint->getSprintGoals()->get(0);

        if (!$firstSprintGoal) {
            throw new RuntimeException('Sprint must have at least one sprint goal');
        }

        $lastOrder = $this->sprintGoalIssueRepository->findLastOrder($firstSprintGoal);

        $sprintGoalIssue = new SprintGoalIssue(
            sprintGoal: $firstSprintGoal,
            issue: $issue,
            goalOrder: $lastOrder + SprintGoalIssue::DEFAULT_ORDER_SPACE
        );

        $issue->setIssueColumn($this->issueColumnRepository->toDoColumn());

        foreach ($issue->getSubIssues() as $subIssue) {
            $subIssue->setIssueColumn($this->issueColumnRepository->toDoColumn());
        }

        $this->entityManager->persist($sprintGoalIssue);

        $firstSprintGoal->addSprintGoalIssue($sprintGoalIssue);

        $this->entityManager->flush();
    }

    public function removeSprintIssue(Issue $issue): void
    {
        if ($issue->isSubIssue()) {
            throw new RuntimeException('Cannot remove sub issue');
        }

        $sprintGoalIssue = $this->sprintGoalIssueRepository->findSprintIssue($issue, $this->sprint);

        if (!$sprintGoalIssue) {
            throw new RuntimeException('Sprint goal issue not found');
        }

        $issue->setIssueColumn($this->issueColumnRepository->backlogColumn());

        foreach ($issue->getSubIssues() as $subIssue) {
            $subIssue->setIssueColumn($this->issueColumnRepository->backlogColumn());
        }

        $this->entityManager->remove($sprintGoalIssue);

        $this->entityManager->flush();
    }

    public function addGoal(CreateSprintGoalForm $sprintGoalForm): void
    {
        $lastOrder = $this->sprintGoalRepository->findLastOrder($this->sprint);

        $newSprintGoal = new SprintGoal(
            name: $sprintGoalForm->name,
            sprintOrder: $lastOrder + SprintGoal::DEFAULT_ORDER_SPACE,
            sprint: $this->sprint
        );

        $this->entityManager->persist($newSprintGoal);

        $this->entityManager->flush();
    }

    public function removeSprintGoal(SprintGoal $sprintGoal): void
    {
        foreach ($sprintGoal->getSprintGoalIssues() as $goalIssue) {
            $this->entityManager->remove($goalIssue);
        }

        $this->entityManager->remove($sprintGoal);

        $this->entityManager->flush();
    }

    public function start(): void
    {
        if (!$this->sprint->isCurrent()) {
            throw new RuntimeException('Only current sprint can be started');
        }

        if ($this->sprint->isStarted()) {
            throw new RuntimeException('Cannot start sprint. Sprint already started');
        }

        foreach ($this->sprint->getSprintGoals() as $sprintGoal) {
            if ($sprintGoal->getSprintGoalIssues()->isEmpty()) {
                throw new CannotStartSprintException(
                    'Cannot start sprint. Every sprint goal must have at least one issue.'
                );
            }
        }

        $this->sprint->setStartedAt($this->clock->now());

        $this->entityManager->flush();
    }

}
