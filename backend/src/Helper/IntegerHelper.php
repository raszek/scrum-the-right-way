<?php

namespace App\Helper;

use App\Exception\Helper\CouldNotConvertToIntegerException;

class IntegerHelper
{

    public static function parseInt(string $textInteger): int
    {
        if ($textInteger === '0') {
            return 0;
        }

        $val = intval($textInteger);

        if ($val === 0) {
            throw new CouldNotConvertToIntegerException('Could not convert text to integer');
        }

        return $val;
    }

    public static function isInteger(string $textInteger): bool
    {
        try {
            self::parseInt($textInteger);
        } catch (CouldNotConvertToIntegerException) {
            return false;
        }

        return true;
    }

}
