<?php

namespace App\Service\Sprint;

use App\Entity\Issue\Issue;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Exception\Sprint\CannotAddSprintIssueException;
use App\Exception\Sprint\CannotStartSprintException;
use App\Form\Sprint\CreateSprintGoalForm;
use App\Form\Sprint\StartSprintForm;
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
        private SprintService $sprintService,
    ) {
    }

    public function addSprintIssues(array $issues): void
    {
        if (!$this->sprint->isCurrent()) {
            throw new CannotAddSprintIssueException('Issue can be added only to current sprint');
        }

        foreach ($issues as $issue) {
            $this->addSprintIssue($issue);
        }
    }

    public function removeSprintIssue(Issue $issue): void
    {
        if ($issue->isSubIssue()) {
            throw new RuntimeException('Cannot remove sub issue');
        }

        $issue->setIssueColumn($this->issueColumnRepository->backlogColumn());

        $issueIds = [$issue->getId()];
        foreach ($issue->getSubIssues() as $subIssue) {
            $subIssue->setIssueColumn($this->issueColumnRepository->backlogColumn());
            $issueIds[] = $subIssue->getId();
        }

        $sprintGoalIssues = $this->sprintGoalIssueRepository->findSprintIssues($issueIds, $this->sprint);
        foreach ($sprintGoalIssues as $sprintGoalIssue) {
            $this->entityManager->remove($sprintGoalIssue);
        }

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
            $goalIssue->getIssue()->setIssueColumn($this->issueColumnRepository->backlogColumn());

            $this->entityManager->remove($goalIssue);
        }

        $this->entityManager->remove($sprintGoal);

        $this->entityManager->flush();
    }

    public function start(StartSprintForm $form): void
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

        $count = $this->sprintGoalIssueRepository->countNonEstimatedStoryPointsIssues($this->sprint);
        if ($count > 0) {
            throw new CannotStartSprintException(
                'Cannot start sprint. Every issue and feature sub issue must have story points estimation.'
            );
        }

        $this->sprint->setStartedAt($this->clock->now());
        $this->sprint->setEstimatedEndDate($form->estimatedEndDate);

        $this->entityManager->flush();
    }

    public function finish(): void
    {
        if (!$this->sprint->isCurrent()) {
            throw new RuntimeException('Only current sprint can be finished');
        }

        if (!$this->sprint->isStarted()) {
            throw new RuntimeException('Cannot finish sprint. Sprint is not started');
        }

        $this->sprint->setIsCurrent(false);
        $this->sprint->setEndedAt($this->clock->now());

        $this->finishSprintIssues();

        $this->sprintService->createSprint($this->sprint->getProject());
    }

    /**
     * @param Issue $issue
     * @return void
     * @throws CannotAddSprintIssueException
     */
    private function addSprintIssue(Issue $issue): void
    {
        if ($issue->getProject()->getId() !== $this->sprint->getProject()->getId()) {
            throw CannotAddSprintIssueException::create($issue, 'Issue is from other project');
        }

        if (!$issue->getIssueColumn()->isBacklog()) {
            throw CannotAddSprintIssueException::create($issue, 'You can add only issues from backlog column');
        }

        if ($issue->isSubIssue()) {
            throw CannotAddSprintIssueException::create($issue, 'Cannot add sub issue to sprint');
        }

        $firstSprintGoal = $this->sprint->getSprintGoals()->get(0);

        if (!$firstSprintGoal) {
            throw new CannotAddSprintIssueException('Sprint must have at least one sprint goal');
        }

        $lastOrder = $this->sprintGoalIssueRepository->findLastOrder($firstSprintGoal);

        $sprintGoalIssue = new SprintGoalIssue(
            sprintGoal: $firstSprintGoal,
            issue: $issue,
            goalOrder: $lastOrder + SprintGoalIssue::DEFAULT_ORDER_SPACE
        );

        $issue->setIssueColumn($this->issueColumnRepository->toDoColumn());

        $this->entityManager->persist($sprintGoalIssue);
        $firstSprintGoal->addSprintGoalIssue($sprintGoalIssue);

        foreach ($issue->getSubIssues() as $subIssue) {
            $subIssue->setIssueColumn($this->issueColumnRepository->toDoColumn());
            $sprintGoalSubIssue = new SprintGoalIssue(
                sprintGoal: $firstSprintGoal,
                issue: $subIssue,
            );
            $this->entityManager->persist($sprintGoalSubIssue);

            $firstSprintGoal->addSprintGoalIssue($sprintGoalSubIssue);
        }

        $this->entityManager->flush();
    }

    private function finishSprintIssues(): void
    {
        $sprintGoalIssues = $this->sprintGoalIssueRepository->findAllSprintIssues($this->sprint);

        foreach ($sprintGoalIssues as $sprintGoalIssue) {
            $issue = $sprintGoalIssue->getIssue();
            if ($sprintGoalIssue->getFinishedAt() !== null) {
                $issue->setIssueColumn($this->issueColumnRepository->finishedColumn());
            } else {
                $issue->setPreviousStoryPoints($issue->getStoryPoints());
                $issue->setIssueColumn($this->issueColumnRepository->backlogColumn());
                $issue->setStoryPoints(null);
            }
        }
    }

}
