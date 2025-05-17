<?php

namespace App\DataFixtures;

use App\Factory\Issue\IssueColumnFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IssueColumnFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        IssueColumnFactory::createColumns();
    }
}
