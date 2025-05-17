<?php

namespace App\ValueObject;

use Assert\Assertion;

readonly class Color
{
    public function __construct(
        private int $red,
        private int $green,
        private int $blue,
    ) {
        Assertion::range($this->red, 0, 255);
        Assertion::range($this->green, 0, 255);
        Assertion::range($this->blue, 0, 255);
    }

    public function formatHex(): string
    {
        return sprintf("#%02x%02x%02x", $this->red, $this->green, $this->blue);
    }

    public static function fromHex(string $hex): Color
    {
        if ($hex[0] === '#') {
            $hex = substr($hex, 1);
        }

        if (strlen($hex) === 8) {
            $hex = substr($hex, 0, 6);
        }

        if (strlen($hex) === 3) {
            [$r, $g, $b] = array_map(fn ($char) => str_pad($char, 2, $char), str_split($hex));
        } else {
            [$r, $g, $b] = str_split($hex, 2);
        }

        return new Color(
            red: hexdec($r),
            green: hexdec($g),
            blue: hexdec($b),
        );
    }
}
