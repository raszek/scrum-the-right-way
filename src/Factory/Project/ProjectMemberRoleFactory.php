<?php

namespace App\Factory\Project;

use App\Entity\Project\ProjectMemberRole;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProjectMemberRole>
 */
final class ProjectMemberRoleFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return ProjectMemberRole::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'projectMember' => ProjectMemberFactory::new(),
            'role' => ProjectRoleFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ProjectMemberRole $projectMemberRole): void {})
        ;
    }
}
