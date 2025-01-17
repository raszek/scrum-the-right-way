<?php

namespace App\Factory\Project;

use App\Entity\Project\ProjectRole;
use App\Enum\Project\ProjectRoleEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProjectRole>
 */
final class ProjectRoleFactory extends PersistentProxyObjectFactory
{

    public static function projectRoles(): void
    {
        foreach (ProjectRoleEnum::cases() as $projectRole) {
            self::createOne([
                'id' => $projectRole->value,
                'label' => $projectRole->label()
            ]);
        }
    }

    public static function analyticRole(): ProjectRole
    {
        return self::findOrCreate([
            'id' => ProjectRoleEnum::Analytic->value,
            'label' => ProjectRoleEnum::Analytic->label(),
        ]);
    }

    public static function developerRole(): ProjectRole
    {
        return self::findOrCreate([
            'id' => ProjectRoleEnum::Developer->value,
            'label' => ProjectRoleEnum::Developer->label(),
        ]);
    }

    public static function adminRole(): ProjectRole
    {
        return self::findOrCreate([
            'id' => ProjectRoleEnum::Admin->value,
            'label' => ProjectRoleEnum::Admin->label(),
        ]);
    }

    public static function class(): string
    {
        return ProjectRole::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'id' => self::faker()->randomNumber(),
            'label' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
        ;
    }
}
