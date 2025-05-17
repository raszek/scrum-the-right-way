<?php

namespace App\Service\Sprint;


use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Repository\Sprint\SprintRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SprintService
{

    public function __construct(
        private SprintRepository $sprintRepository,
        private SprintGoalRepository $sprintGoalRepository,
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Sprint $sprint
     * @return SprintGoal[]
     */
    public function getSprintGoals(Sprint $sprint): array
    {
        return $this->sprintGoalRepository->getSprintGoals($sprint);
    }

    public function getLatestDoneIssues(Sprint $sprint): array
    {
        return $this->sprintGoalIssueRepository->getLatestDoneIssues($sprint);
    }

    public function createSprint(Project $project): void
    {
        $nextSprintNumber = $this->sprintRepository->getNextSprintNumber($project);

        $sprint = new Sprint(
            number: $nextSprintNumber,
            isCurrent: true,
            project: $project
        );

        $sprintGoal = new SprintGoal(
            name: 'Define your sprint goal',
            sprintOrder: SprintGoal::DEFAULT_ORDER_SPACE,
            sprint: $sprint
        );

        $this->entityManager->persist($sprint);
        $this->entityManager->persist($sprintGoal);

        $this->entityManager->flush();
    }

}
