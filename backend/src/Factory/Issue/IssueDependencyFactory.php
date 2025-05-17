<?php

namespace App\Factory\Issue;

use App\Entity\Issue\IssueDependency;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<IssueDependency>
 */
final class IssueDependencyFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return IssueDependency::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'dependency' => IssueFactory::new(),
            'issue' => IssueFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
        ;
    }
}
