<?php

namespace App\Helper;

class StimulusHelper
{

    public static function boolean(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    public static function object(mixed $value): string
    {
        if ($value === null) {
            return self::nullObject();
        }

        return JsonHelper::encode($value);
    }

    public static function nullObject(): string
    {
        return '{}';
    }

}
