<?php

namespace App\DataFixtures;

use App\Factory\Project\ProjectTypeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        ProjectTypeFactory::createProjectTypes();
    }

    public static function getGroups(): array
    {
        return ['install'];
    }
}
