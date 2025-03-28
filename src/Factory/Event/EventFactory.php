<?php

namespace App\Factory\Event;

use App\Entity\Event\Event;
use App\Event\EventInterface;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Event>
 */
final class EventFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Event::class;
    }

    public function withEvent(EventInterface $event): EventFactory
    {
        return $this->with([
            'name' => $event->name(),
            'params' => $event->toArray(),
        ]);
    }

    protected function defaults(): array|callable
    {
        return [
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name' => self::faker()->text(255),
            'project' => ProjectFactory::new(),
            'createdBy' => UserFactory::new(),
            'params' => []
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
