<?php

namespace App\Service\Session\Websocket;

use App\Service\Jwt\Websocket\WebsocketJwtServiceFactory;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class WebsocketSessionFactory
{

    public function __construct(
        private RequestStack $requestStack,
        private WebsocketJwtServiceFactory $factory
    ) {
    }

    public function create(): WebsocketSession
    {
        return new WebsocketSession(
            requestStack: $this->requestStack,
            jwtService: $this->factory->create(),
        );
    }

}
