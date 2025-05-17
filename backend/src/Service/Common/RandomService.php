<?php

namespace App\Service\Common;

use App\ValueObject\Color;
use Symfony\Component\String\ByteString;

class RandomService
{

    public function randomColor(): Color
    {
        return new Color(
            red: $this->randomInteger(0, 255),
            green: $this->randomInteger(0, 255),
            blue: $this->randomInteger(0, 255),
        );
    }

    public function randomElement(array $elements): mixed
    {
        return $elements[array_rand($elements)];
    }

    public function randomBoolean(): bool
    {
        return $this->randomInteger(0, 1) === 0;
    }

    public function randomInteger(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public function randomElements(array $elements, int $numberElements): array
    {
        if ($numberElements <= 0) {
            return [];
        }

        if ($numberElements === 1) {
            return [$this->randomElement($elements)];
        }

        $randomIndexes = array_rand($elements, $numberElements);

        $result = [];
        foreach ($randomIndexes as $randomIndex) {
            $result[] = $elements[$randomIndex];
        }

        return $result;
    }

    public function randomString(int $length = 32): string
    {
        return ByteString::fromRandom($length)->toString();
    }
}
