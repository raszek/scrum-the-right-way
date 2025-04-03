<?php

namespace App\Factory\Project;

use App\Entity\Project\Project;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class ProjectFactory extends PersistentProxyObjectFactory
{

    public static function class(): string
    {
        return Project::class;
    }

    public function withKanbanType(): self
    {
        return $this->with([
            'type' => ProjectTypeFactory::kanbanType()
        ]);
    }

    public function withScrumType(): self
    {
        return $this->with([
            'type' => ProjectTypeFactory::scrumType()
        ]);
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(255),
            'code' => strtoupper(self::faker()->lexify('???')),
            'type' => ProjectTypeFactory::scrumType(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Project $project): void {})
        ;
    }
}
