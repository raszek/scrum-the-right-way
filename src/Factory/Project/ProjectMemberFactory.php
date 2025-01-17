<?php

namespace App\Factory\Project;

use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectRole;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProjectMember>
 */
final class ProjectMemberFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return ProjectMember::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'user' => UserFactory::new(),
            'project' => ProjectFactory::new(),
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
