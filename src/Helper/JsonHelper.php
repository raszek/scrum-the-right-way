<?php

namespace App\Helper;

class JsonHelper
{

    public static function encode(mixed $value): string
    {
        return json_encode($value, flags: JSON_THROW_ON_ERROR);
    }

    public static function decode(string $json): mixed
    {
        return json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
    }

}
