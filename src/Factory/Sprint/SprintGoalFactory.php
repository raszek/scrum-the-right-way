<?php

namespace App\Factory\Sprint;

use App\Entity\Sprint\SprintGoal;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SprintGoal>
 */
final class SprintGoalFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return SprintGoal::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(1024),
            'sprint' => SprintFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(SprintGoal $sprintGoal): void {})
        ;
    }
}
