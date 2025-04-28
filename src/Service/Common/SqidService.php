<?php

namespace App\Service\Common;

use App\Helper\ArrayHelper;
use Exception;
use Sqids\Sqids;

class SqidService
{

    private Sqids $sqids;

    public function __construct(
    ) {
        $this->sqids = new Sqids(minLength: 10);
    }

    public function encode(int $value): string
    {
        return $this->sqids->encode([$value]);
    }

    public function decode(string $value): int
    {
        $ids = $this->sqids->decode($value);

        if (!isset($ids[0])) {
            throw new Exception('Empty ids array. This should not happen');
        }

        return $ids[0];
    }

    /**
     * @param string[] $values
     * @return int[]
     */
    public function decodeMany(array $values): array
    {
        return ArrayHelper::map($values, fn(string $value) => $this->decode($value));
    }

    public static function create(): static
    {
        return new SqidService();
    }

}
