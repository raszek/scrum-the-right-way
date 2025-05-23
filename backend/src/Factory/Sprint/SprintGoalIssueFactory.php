<?php

namespace App\Factory\Sprint;

use App\Entity\Sprint\SprintGoalIssue;
use App\Factory\Issue\IssueFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SprintGoalIssue>
 */
final class SprintGoalIssueFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return SprintGoalIssue::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'issue' => IssueFactory::new(),
            'sprintGoal' => SprintGoalFactory::new(),
            'goalOrder' => self::faker()->randomNumber()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(SprintGoalIssue $sprintGoalIssue): void {})
        ;
    }
}
