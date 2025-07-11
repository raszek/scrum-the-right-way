<?php

namespace App\Controller\Room;

use App\Controller\Controller;
use App\Enum\Room\RoomTabEnum;
use App\Service\Room\UserRoomSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user-settings')]
class UserRoomSettingsController extends Controller
{

    public function __construct(
        private readonly UserRoomSettings $userRoomSettings
    ) {
    }

    #[Route('/set-room-tab', 'app_user_settings_set_room_tab', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function setRoomTab(Request $request): Response
    {
        $tab = $request->get('tab');

        if (!$tab) {
            throw new UnprocessableEntityHttpException('Tab cannot be empty.');
        }

        $tabEnum = RoomTabEnum::tryFrom($tab);
        if (!$tabEnum) {
            throw new UnprocessableEntityHttpException('Invalid tab value');
        }

        $this->userRoomSettings->setTab($tabEnum);

        return new Response(status: 204);
    }
}
