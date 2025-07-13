<?php

namespace App\Helper;

class ArrayHelper
{

    public static function inArray(mixed $needle, array $haystack): bool
    {
        return in_array($needle, $haystack, strict: true);
    }

    public static function map(array $items, callable $callback): array
    {
        return array_map($callback, $items);
    }

    public static function filter(array $items, callable $callback): array
    {
        return array_filter($items, $callback);
    }

    public static function indexByKey(array $items, string $key): array
    {
        $mapped = [];
        foreach ($items as $item) {
            $mapped[$item[$key]] = $item;
        }

        return $mapped;
    }

    public static function indexByCallback(array $items, callable $callback): array
    {
        $mapped = [];
        foreach ($items as $item) {
            $mapped[$callback($item)] = $item;
        }

        return $mapped;
    }
}
