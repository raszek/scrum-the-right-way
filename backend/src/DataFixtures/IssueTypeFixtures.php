<?php

namespace App\DataFixtures;

use App\Factory\Issue\IssueTypeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IssueTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        IssueTypeFactory::createTypes();
    }
}
