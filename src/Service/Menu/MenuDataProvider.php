<?php

namespace App\Service\Menu;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class MenuDataProvider
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getLinks(string $currentPath): array
    {
        $links = [];
        foreach ($this->getMenuLinks() as $menuLink) {
            $links[] = [
                'isActive' => $menuLink['url'] === $currentPath,
                ...$menuLink
            ];
        }

        return $links;
    }

    private function getMenuLinks(): array
    {
        return [
            [
                'url' => $this->urlGenerator->generate('app_project_list'),
                'label' => 'Projects'
            ]
        ];
    }
}
