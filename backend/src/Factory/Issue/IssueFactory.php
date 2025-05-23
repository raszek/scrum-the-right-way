<?php

namespace App\Factory\Issue;

use App\Entity\Issue\Issue;
use App\Enum\Issue\IssueTypeEnum;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Issue>
 */
final class IssueFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Issue::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'createdBy' => UserFactory::new(),
            'issueColumn' => IssueColumnFactory::backlogColumn(),
            'type' => IssueTypeFactory::issueType(),
            'number' => 1,
            'columnOrder' => 128,
            'project' => ProjectFactory::new(),
            'title' => self::faker()->text(255),
            'updatedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->beforeInstantiate(function (array $parameters) {
            if ($parameters['type']->getId() === IssueTypeEnum::SubIssue->value && !isset($parameters['issueOrder'])) {
                $parameters['issueOrder'] = 128;
            }

            return $parameters;
        });
    }
}
