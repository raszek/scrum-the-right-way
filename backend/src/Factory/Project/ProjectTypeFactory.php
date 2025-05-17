<?php

namespace App\Factory\Project;

use App\Entity\Project\ProjectType;
use App\Enum\Project\ProjectTypeEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProjectType>
 */
final class ProjectTypeFactory extends PersistentProxyObjectFactory
{

    public static function createProjectTypes(): void
    {
        $projectTypes = ProjectTypeEnum::cases();

        foreach ($projectTypes as $projectType) {
            self::createOne([
                'id' => $projectType->value,
                'label' => $projectType->label()
            ]);
        }
    }

    public static function kanbanType(): ProjectType
    {
        return self::findOrCreate([
            'id' => ProjectTypeEnum::Kanban->value,
            'label' => ProjectTypeEnum::Kanban->label()
        ]);
    }

    public static function scrumType(): ProjectType
    {
        return self::findOrCreate([
            'id' => ProjectTypeEnum::Scrum->value,
            'label' => ProjectTypeEnum::Scrum->label()
        ]);
    }

    public static function class(): string
    {
        return ProjectType::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'id' => ProjectTypeEnum::Scrum->value,
            'label' => ProjectTypeEnum::Scrum->label(),
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
