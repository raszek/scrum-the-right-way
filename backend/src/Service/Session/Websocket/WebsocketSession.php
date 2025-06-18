<?php

namespace App\Service\Session\Websocket;

use App\Service\Jwt\Websocket\WebsocketJwtPayload;
use App\Service\Jwt\Websocket\WebsocketJwtService;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class WebsocketSession
{

    const string WEBSOCKET_TOKEN_KEY = 'WEBSOCKET_TOKEN_KEY';

    public function __construct(
        private RequestStack $requestStack,
        private WebsocketJwtService $jwtService,
    ) {
    }

    public function getToken(WebsocketJwtPayload $payload): string
    {
        $currentToken = $this->getCurrentToken();

        if ($currentToken) {
            return $currentToken;
        }

        return $this->recreateToken($payload);
    }

    public function recreateToken(WebsocketJwtPayload $payload): string
    {
        $jwtToken = $this->jwtService->encode($payload);
        $this->requestStack->getSession()->set(self::WEBSOCKET_TOKEN_KEY, $jwtToken);

        return $jwtToken;
    }

    public function getCurrentToken(): ?string
    {
        $currentJwtToken = $this->requestStack->getSession()->get(self::WEBSOCKET_TOKEN_KEY);

        if (!$currentJwtToken || $this->jwtService->isTokenExpired($currentJwtToken)) {
            return null;
        }

        return $currentJwtToken;
    }
}
