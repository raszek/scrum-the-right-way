<?php

namespace App\Service\Jwt\Websocket;

readonly class WebsocketJwtPayload
{

    public function __construct(
        public string $id,
        public string $fullName
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
        ];
    }

}
