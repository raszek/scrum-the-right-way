<?php

namespace App\DataFixtures;

use App\Entity\Project\Project;
use App\Factory\Issue\IssueDependencyFactory;
use App\Repository\Issue\IssueRepository;
use App\Repository\Project\ProjectRepository;
use App\Service\Common\RandomService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IssueDependencyFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly RandomService $randomService
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $projects = $this->projectRepository->findAll();

        foreach ($projects as $project) {
            $this->loadProjectDependencies($project);
        }
    }

    private function loadProjectDependencies(Project $project): void
    {
        $issues = $this->issueRepository->findBy([
            'project' => $project,
        ], orderBy: [
            'number' => 'ASC'
        ]);

        for ($i = 0; $i < count($issues); $i+=2) {
            if ($this->randomService->randomBoolean()) {
                IssueDependencyFactory::createOne([
                    'issue' => $issues[$i],
                    'dependency' => $issues[$i + 1],
                ]);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            IssueFixtures::class
        ];
    }
}
