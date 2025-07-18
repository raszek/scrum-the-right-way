<?php

namespace App\Service\Room;

use App\Enum\Room\RoomTabEnum;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class UserRoomSettings
{

    CONST string ROOM_SELECTED_TAB_KEY = 'ROOM_SELECTED_TAB_KEY';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function getTab(): RoomTabEnum
    {
        $session = $this->requestStack->getSession();

        $tab = $session->get(self::ROOM_SELECTED_TAB_KEY);

        if (!$tab) {
            return RoomTabEnum::Users;
        }

        return RoomTabEnum::tryFrom($tab);
    }

    public function setTab(RoomTabEnum $tabEnum): void
    {
        $session = $this->requestStack->getSession();

        $session->set(self::ROOM_SELECTED_TAB_KEY, $tabEnum->value);
    }
}
