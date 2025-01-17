<?php

namespace App\DataFixtures;

use App\Entity\Project\Project;
use App\Factory\Project\ProjectTagFactory;
use App\Repository\Project\ProjectRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectTagFixtures extends Fixture implements DependentFixtureInterface
{

    const TAGS = [
        'CODE_REVIEW',
        'SPRINT',
        'PROD',
        'DEV',
        'BLOCKED',
        'TEST',
        'NEEDS_INFO'
    ];

    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $projects = $this->projectRepository->findAll();

        foreach ($projects as $project) {
            $this->loadProjectTags($project);
        }
    }

    private function loadProjectTags(Project $project): void
    {
        foreach (self::TAGS as $tagName) {
            ProjectTagFactory::createOne([
                'project' => $project,
                'name' => $tagName
            ]);
        }
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class
        ];
    }
}
