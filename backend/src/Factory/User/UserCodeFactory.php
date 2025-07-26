<?php

namespace App\Factory\User;

use App\Entity\User\UserCode;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<UserCode>
 */
final class UserCodeFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return UserCode::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'code' => self::faker()->text(255),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'user' => UserFactory::new(),
            'type' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(UserCode $userCode): void {})
        ;
    }
}
