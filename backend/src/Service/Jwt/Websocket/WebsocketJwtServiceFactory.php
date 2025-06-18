<?php

namespace App\Service\Jwt\Websocket;

use App\Service\Jwt\JwtServiceFactory;

readonly class WebsocketJwtServiceFactory
{
    public function __construct(
        private string $websocketBaseUrl,
        private JwtServiceFactory $factory
    ) {
    }

    public function create(): WebsocketJwtService
    {
        return new WebsocketJwtService(
            jwtService: $this->factory->create($this->websocketBaseUrl)
        );
    }

}
