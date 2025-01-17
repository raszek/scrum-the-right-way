<?php

namespace App\Factory\Thread;

use App\Entity\Thread\ThreadStatus;
use App\Enum\Thread\ThreadStatusEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ThreadStatus>
 */
final class ThreadStatusFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return ThreadStatus::class;
    }

    public static function openStatus(): ThreadStatus
    {
        return ThreadStatusFactory::findOrCreate([
            'id' => ThreadStatusEnum::Open->value,
            'label' => ThreadStatusEnum::Open->label()
        ]);
    }
    public static function threadStatuses(): void
    {
        foreach (ThreadStatusEnum::cases() as $threadStatusCase) {
            ThreadStatusFactory::createOne([
                'label' => $threadStatusCase->label(),
                'id' => $threadStatusCase->value
            ]);
        }
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'id' => ThreadStatusEnum::Open->value,
            'label' => ThreadStatusEnum::Open->label(),
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
