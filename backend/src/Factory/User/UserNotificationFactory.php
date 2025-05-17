<?php

namespace App\Factory\User;

use App\Entity\User\UserNotification;
use App\Factory\Event\EventFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<UserNotification>
 */
final class UserNotificationFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return UserNotification::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'event' => EventFactory::new(),
            'forUser' => UserFactory::new(),
            'read' => false,
            'sentEmail' => false,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
