<?php

namespace App\Service\Menu\Profile;

use App\Service\Menu\Menu;
use App\Service\Menu\MenuFactory;
use App\Service\Menu\MenuLink;
use App\Service\Menu\MenuUrl;

readonly class ProfileMenu
{

    public function __construct(
        private MenuFactory $menuFactory,
    ) {
    }

    public function create(string $currentPath): Menu
    {
        $profileLinks = [
            new MenuLink(
                label: 'Profile',
                url: new MenuUrl('app_user_profile')
            ),
            new MenuLink(
                label: 'Change password',
                url: new MenuUrl('app_user_profile_change_password')
            ),
        ];

        return $this->menuFactory->create($profileLinks, $currentPath);
    }

}
