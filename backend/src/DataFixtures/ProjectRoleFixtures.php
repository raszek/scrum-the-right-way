<?php

namespace App\DataFixtures;

use App\Factory\Project\ProjectRoleFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectRoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ProjectRoleFactory::projectRoles();
    }
}
