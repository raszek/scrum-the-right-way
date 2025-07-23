<?php

namespace App\DataFixtures;

use App\Factory\Project\ProjectRoleFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectRoleFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        ProjectRoleFactory::projectRoles();
    }

    public static function getGroups(): array
    {
        return ['install'];
    }
}
