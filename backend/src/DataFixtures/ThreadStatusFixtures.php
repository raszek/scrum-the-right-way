<?php

namespace App\DataFixtures;

use App\Factory\Thread\ThreadStatusFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ThreadStatusFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        ThreadStatusFactory::threadStatuses();
    }

    public static function getGroups(): array
    {
        return ['install'];
    }
}
