<?php

namespace App\DataFixtures;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Factory\Issue\IssueFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Sprint\SprintGoalFactory;
use App\Factory\Sprint\SprintGoalIssueFactory;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueTypeRepository;
use App\Repository\Project\ProjectRepository;
use App\Service\Common\ClockInterface;
use App\Service\Common\RandomService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SprintFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly ClockInterface $clock,
        private readonly IssueTypeRepository $issueTypeRepository,
        private readonly IssueColumnRepository $issueColumnRepository,
        private readonly RandomService $randomService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->projectRepository->findAll() as $project) {
            $this->loadSprints($project);
        }
    }

    private function loadSprints(Project $project): void
    {
        if (!$project->isScrum()) {
            return;
        }

        $now = $this->clock->now();

        foreach (range(1, 4) as $number) {
            $sprintDateBegin = $now->subWeeks(12 - $number);
            $sprintDateEnd = $now->subWeeks(11 - $number);

            $sprint = SprintFactory::createOne([
                'project' => $project,
                'startedAt' => $sprintDateBegin,
                'estimatedEndDate' => $sprintDateEnd,
                'endedAt' => $sprintDateEnd,
                'number' => $number,
                'isCurrent' => false
            ]);

            $sprintGoal = SprintGoalFactory::createOne([
                'sprint' => $sprint,
                'sprintOrder' => 1024
            ]);

            foreach (range(1, 10) as $j) {
                $isIssueFinished = $this->randomService->randomInteger(0, 100) > 25;

                $issueNumber = 140 + ($number * 10) + $j;

                $issue = IssueFactory::createOne([
                    'number' => $issueNumber,
                    'project' => $project,
                    'type' => $this->issueTypeRepository->issueType(),
                    'issueColumn' => $isIssueFinished
                        ? $this->issueColumnRepository->backlogColumn()
                        : $this->issueColumnRepository->finishedColumn(),
                    'storyPoints' => $this->randomService->randomElement([1, 2, 3, 5, 8, 13, 21]),
                    'columnOrder' => $issueNumber * Issue::DEFAULT_ORDER_SPACE
                ]);

                SprintGoalIssueFactory::createOne([
                    'issue' => $issue,
                    'sprintGoal' => $sprintGoal,
                    'finishedAt' => $isIssueFinished
                        ? $sprintDateBegin->addDays($this->randomService->randomInteger(1, 6))
                        : null,
                ]);
            }
        }

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'number' => 5,
            'isCurrent' => true
        ]);

        SprintGoalFactory::createOne([
            'sprint' => $sprint,
            'sprintOrder' => 1024
        ]);
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            IssueFixtures::class,
            IssueTypeFixtures::class,
            IssueColumnFixtures::class,
        ];
    }
}
