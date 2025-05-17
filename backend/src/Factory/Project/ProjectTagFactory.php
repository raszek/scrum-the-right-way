<?php

namespace App\Factory\Project;

use App\Entity\Project\ProjectTag;
use App\Service\Common\RandomService;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ProjectTag>
 */
final class ProjectTagFactory extends PersistentProxyObjectFactory
{

    public function __construct(
        private readonly RandomService $randomService
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return ProjectTag::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'backgroundColor' => $this->randomService->randomColor(),
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
