<?php

namespace App\Service\Event;

use App\Event\EventRendererInterface;
use App\Event\FullEventList;
use App\Event\IssueEventRendererInterface;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class EventRendererFactory implements ServiceSubscriberInterface
{

    public function __construct(
        private ContainerInterface $locator,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return array_values(FullEventList::renderers());
    }

    public function getIssueEventRenderer(string $eventName): IssueEventRendererInterface
    {
        $renderer = $this->getRenderer($eventName);

        if (!$renderer instanceof IssueEventRendererInterface) {
            throw new Exception('Renderer does not implement issue event renderer');
        }

        return $renderer;
    }

    public function getEventRenderer(string $eventName): EventRendererInterface
    {
        return $this->getRenderer($eventName);
    }

    private function getRenderer(string $eventName): EventRendererInterface
    {
        $rendererClassName = $this->findRendererClassName($eventName);

        return $this->locator->get($rendererClassName);
    }

    private function findRendererClassName(string $eventName): string
    {
        $renderers = FullEventList::renderers();

        if (!isset($renderers[$eventName])) {
            throw new Exception(sprintf('Event renderer "%s" not found', $eventName));
        }

        return $renderers[$eventName];
    }
}
