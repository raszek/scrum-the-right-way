<?php

namespace App\DataFixtures;

use App\Factory\Thread\ThreadStatusFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThreadStatusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ThreadStatusFactory::threadStatuses();
    }
}
