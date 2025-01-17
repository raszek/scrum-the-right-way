<?php

namespace App\Factory\Issue;

use App\Entity\Issue\IssueColumn;
use App\Enum\Issue\IssueColumnEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<IssueColumn>
 */
final class IssueColumnFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return IssueColumn::class;
    }

    public static function createColumns(): void
    {
        foreach (IssueColumnEnum::cases() as $case) {
            IssueColumnFactory::createOne([
                'id' => $case->value,
                'label' => $case->label()
            ]);
        }
    }

    public static function testColumn(): IssueColumn
    {
        return IssueColumnFactory::createOne([
            'id' => IssueColumnEnum::Test->value,
            'label' => IssueColumnEnum::Test->label()
        ]);
    }

    public static function todoColumn(): IssueColumn
    {
        return IssueColumnFactory::createOne([
            'id' => IssueColumnEnum::ToDo->value,
            'label' => IssueColumnEnum::ToDo->label()
        ]);
    }

    public static function backlogColumn(): IssueColumn
    {
        return IssueColumnFactory::createOne([
            'id' => IssueColumnEnum::Backlog->value,
            'label' => IssueColumnEnum::Backlog->label()
        ]);
    }

    protected function defaults(): array|callable
    {
        return [
            'id' => self::faker()->randomNumber(),
            'label' => self::faker()->text(255),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
