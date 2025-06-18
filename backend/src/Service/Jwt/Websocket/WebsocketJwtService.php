<?php

namespace App\Service\Jwt\Websocket;

use App\Service\Jwt\JwtService;

readonly class WebsocketJwtService
{
    public function __construct(
        private JwtService $jwtService,
    ) {
    }

    public function encode(WebsocketJwtPayload $payload): string
    {
        return $this->jwtService->encode($payload->toArray());
    }

    public function isTokenExpired(string $token): bool
    {
        return $this->jwtService->isTokenExpired($token);
    }
}
