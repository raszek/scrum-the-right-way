<?php

namespace App\Factory\Issue;

use App\Entity\Issue\Attachment;
use App\Factory\FileFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Attachment>
 */
final class AttachmentFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Attachment::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'file' => FileFactory::new(),
            'issue' => IssueFactory::new(),
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
