<?php

namespace App\Factory\Issue;

use App\Entity\Issue\IssueObserver;
use App\Factory\Project\ProjectMemberFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<IssueObserver>
 */
final class IssueObserverFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return IssueObserver::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'issue' => IssueFactory::new(),
            'projectMember' => ProjectMemberFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this;
    }
}
