<?php

namespace App\Factory\Thread;

use App\Entity\Thread\ThreadMessage;
use App\Factory\UserFactory;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ThreadMessage>
 */
final class ThreadMessageFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return ThreadMessage::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'content' => self::faker()->text(),
            'number' => self::faker()->randomNumber(),
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'createdBy' => UserFactory::new(),
            'thread' => ThreadFactory::new(),
            'updatedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ThreadMessage $threadMessage): void {})
        ;
    }
}
