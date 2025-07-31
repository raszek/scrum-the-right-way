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

    public static function readableBytes(int $bytes): string
    {
        $i = floor(log($bytes) / log(1024));

        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }

}
