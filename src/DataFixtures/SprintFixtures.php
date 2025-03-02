<?php

namespace App\DataFixtures;

use App\Entity\Project\Project;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Sprint\SprintGoalFactory;
use App\Repository\Project\ProjectRepository;
use App\Service\Common\ClockInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SprintFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly ClockInterface $clock,
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
        if ($project->isKanban()) {
            return;
        }

        $now = $this->clock->now();

        foreach (range(1, 10) as $number) {
            $sprint = SprintFactory::createOne([
                'project' => $project,
                'startedAt' => $now->subWeeks(12 - $number),
                'endedAt' => $now->subWeeks(11 - $number),
                'number' => $number,
                'isCurrent' => false
            ]);

            SprintGoalFactory::createOne([
                'sprint' => $sprint,
            ]);
        }

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'number' => 11,
            'isCurrent' => true
        ]);

        SprintGoalFactory::createOne([
            'sprint' => $sprint,
        ]);
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
        ];
    }
}
