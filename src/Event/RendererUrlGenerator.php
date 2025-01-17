<?php

namespace App\Event;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class RendererUrlGenerator
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function generate(string $name, array $parameters = []): string
    {
        return $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

}
