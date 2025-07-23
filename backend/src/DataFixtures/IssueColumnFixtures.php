<?php

namespace App\DataFixtures;

use App\Factory\Issue\IssueColumnFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class IssueColumnFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        IssueColumnFactory::createColumns();
    }

    public static function getGroups(): array
    {
        return ['install'];
    }
}
