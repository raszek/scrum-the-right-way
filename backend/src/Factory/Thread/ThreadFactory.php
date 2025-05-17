<?php

namespace App\Factory\Thread;

use App\Entity\Thread\Thread;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Thread>
 */
final class ThreadFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return Thread::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        $title = self::faker()->sentence();

        return [
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'createdBy' => UserFactory::new(),
            'project' => ProjectFactory::new(),
            'status' => ThreadStatusFactory::openStatus(),
            'title' => $title,
            'slug' => $this->slugger->slug(mb_strtolower($title)),
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
