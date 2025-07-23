<?php

namespace App\DataFixtures;

use App\Factory\Issue\IssueTypeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class IssueTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        IssueTypeFactory::createTypes();
    }

    public static function getGroups(): array
    {
        return ['install'];
    }
}
