<?php

namespace App\Service\Websocket;

use App\Entity\User\User;
use App\Service\Jwt\Websocket\WebsocketJwtPayload;
use App\Service\Session\Websocket\WebsocketSessionFactory;

readonly class WebsocketService
{

    public function __construct(
        private string $baseUrl,
        private WebsocketSessionFactory $websocketSessionFactory,
    ) {
    }

    public function getUrlConnection(string $path, User $user): string
    {
        $jwtToken = $this->getJwtToken($user);

        return $this->path($path, $jwtToken);
    }

    private function path(string $path, string $token): string
    {
        return sprintf('%s/%s?token=%s', $this->getWebsocketUrl(), $path, $token);
    }

    private function getWebsocketUrl(): string
    {
        return sprintf('ws://%s/websocket', $this->baseUrl);
    }

    private function getJwtToken(User $user): string
    {
        $websocketSession = $this->websocketSessionFactory->create();

        $payload = new WebsocketJwtPayload(
            id: $user->getId(),
            fullName: $user->getFullName(),
        );

        return $websocketSession->getToken($payload);
    }
}
