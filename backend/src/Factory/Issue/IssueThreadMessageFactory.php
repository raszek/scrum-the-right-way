<?php

namespace App\Factory\Issue;

use App\Entity\Issue\IssueThreadMessage;
use App\Factory\Thread\ThreadMessageFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<IssueThreadMessage>
 */
final class IssueThreadMessageFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return IssueThreadMessage::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'issue' => IssueFactory::new(),
            'threadMessage' => ThreadMessageFactory::new(),
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
