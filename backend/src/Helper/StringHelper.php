<?php

namespace App\Helper;

class StringHelper
{

    public static function explodeNewLine(string $text): array
    {
        return preg_split('/\r\n|\r|\n/', $text);
    }

    public static function length(string $text): int
    {
        return mb_strlen($text);
    }

}
