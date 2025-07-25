<?php

namespace App\Service\Menu\Provider;

use App\Entity\User\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class TopMenuDataProvider
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getLinks(string $currentPath, User $user): array
    {
        $links = [];
        foreach ($this->getMenuLinks($user) as $menuLink) {
            if (isset($menuLink['canBeAccessed']) && $menuLink['canBeAccessed'] === false) {
                continue;
            }

            $links[] = [
                'isActive' => isset($menuLink['url']) && $menuLink['url'] === $currentPath,
                ...$menuLink
            ];
        }

        return $links;
    }

    private function getMenuLinks(User $user): array
    {
        return [
            [
                'url' => $this->urlGenerator->generate('app_project_list'),
                'label' => 'Projects',
            ],
            [
                'label' => 'Admin',
                'submenu' => [
                    [
                        'url' => $this->urlGenerator->generate('app_admin_user_list'),
                        'label' => 'Users'
                    ]
                ],
                'canBeAccessed' => $user->isAdmin(),
            ],
        ];
    }
}
