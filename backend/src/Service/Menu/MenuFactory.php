<?php

namespace App\Service\Menu;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class MenuFactory
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param MenuLink[] $menuLinks
     * @param string $currentPath
     * @return Menu
     */
    public function create(array $menuLinks, string $currentPath): Menu
    {
        $links = [];

        foreach ($menuLinks as $menuLink) {
            $menuLinkPath = $this->generateUrl($menuLink->url);

            $links[] = new MenuLinkRecord(
                label: $menuLink->label,
                path: $menuLinkPath,
                isActive: $currentPath === $menuLinkPath,
            );
        }

        return new Menu($links);
    }

    private function generateUrl(MenuUrl $url): string
    {
        return $this->urlGenerator->generate($url->name, $url->params);
    }
}
