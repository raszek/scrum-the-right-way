<?php

namespace App\DataFixtures;

use App\Factory\Project\ProjectTypeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ProjectTypeFactory::createProjectTypes();
    }
}
