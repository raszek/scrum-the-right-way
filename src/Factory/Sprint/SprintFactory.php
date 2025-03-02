<?php

namespace App\Factory\Sprint;

use App\Entity\Sprint\Sprint;
use App\Factory\Project\ProjectFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Sprint>
 */
final class SprintFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Sprint::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'number' => self::faker()->randomNumber(),
            'project' => ProjectFactory::new(),
            'isCurrent' => false
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
