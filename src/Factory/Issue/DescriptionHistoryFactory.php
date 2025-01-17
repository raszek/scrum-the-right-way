<?php

namespace App\Factory\Issue;

use App\Entity\Issue\DescriptionHistory;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<DescriptionHistory>
 */
final class DescriptionHistoryFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return DescriptionHistory::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'issue' => IssueFactory::new(),
            'changes' => null,
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime())
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(DescriptionHistory $descriptionHistory): void {})
        ;
    }
}
