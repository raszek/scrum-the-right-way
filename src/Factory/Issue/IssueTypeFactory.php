<?php

namespace App\Factory\Issue;

use App\Entity\Issue\IssueType;
use App\Enum\Issue\IssueTypeEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<IssueType>
 */
final class IssueTypeFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return IssueType::class;
    }

    public static function createTypes(): void
    {
        foreach (IssueTypeEnum::cases() as $case) {
            self::findOrCreate([
                'id' => $case->value,
                'label' => $case->label()
            ]);
        }
    }

    public static function issueType(): IssueType
    {
        return self::findOrCreate([
            'id' => IssueTypeEnum::Issue->value,
            'label' => IssueTypeEnum::Issue->label(),
        ]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'id' => IssueTypeEnum::Issue->value,
            'label' => IssueTypeEnum::Issue->label(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(IssueType $issueType): void {})
        ;
    }
}
