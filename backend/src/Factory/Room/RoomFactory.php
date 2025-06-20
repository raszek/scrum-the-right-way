<?php

namespace App\Factory\Room;

use App\Entity\Room\Room;
use App\Factory\Project\ProjectFactory;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Room>
 */
final class RoomFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Room::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'project' => ProjectFactory::new(),
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Room $room): void {})
        ;
    }
}
