<?php

namespace App\Service\Jwt;

use App\Service\Common\ClockInterface;

readonly class JwtServiceFactory
{

    public function __construct(
        private string $secretKey,
        private string $baseUrl,
        private ClockInterface $clock,
    ) {
    }

    public function create(string $audienceBaseUrl): JwtService
    {
        return new JwtService(
            secretKey: $this->secretKey,
            baseUrl: $this->baseUrl,
            audienceBaseUrl: $audienceBaseUrl,
            clock: $this->clock,
        );
    }

}
