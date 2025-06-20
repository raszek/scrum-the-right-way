<?php

namespace App\Service\Jwt;

use App\Service\Common\ClockInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

readonly class JwtService
{

    const string JWT_ALGORITHM = 'HS256';

    public function __construct(
        private string $secretKey,
        private string $baseUrl,
        private string $audienceBaseUrl,
        private ClockInterface $clock,
    ) {
    }

    public function encode(array $additionalPayload = []): string
    {
        $payload = array_merge(
            $this->getRequiredPayload(),
            $additionalPayload
        );

        return JWT::encode($payload, $this->secretKey, self::JWT_ALGORITHM);
    }

    public function decode(string $token): array
    {
        return (array)JWT::decode($token, new Key($this->secretKey, self::JWT_ALGORITHM));
    }

    public function isTokenExpired(string $token): bool
    {
        try {
            $this->decode($token);
        } catch (ExpiredException) {
            return true;
        }

        return false;
    }

    private function getRequiredPayload(): array
    {
        return [
            'iss' => $this->getISS(),
            'aud' => $this->getAUD(),
            'exp' => $this->clock->now()->getTimestamp() + $this->getExpireTime(),
        ];
    }

    private function getISS(): string
    {
        return $this->baseUrl;
    }

    private function getAUD(): string
    {
        return $this->audienceBaseUrl;
    }

    /**
     * One hour
     * @return int
     */
    private function getExpireTime(): int
    {
        return 3600;
    }
}
