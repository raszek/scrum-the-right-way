<?php

namespace App\Doctrine;

use App\Service\Common\SqidService;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class SqidType extends Type
{

    const TYPE_NAME = 'sqid';

    private SqidService|null $sqids = null;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Sqid
    {
        if ($value === null) {
            return null;
        }

        return new Sqid(
            sqid: $this->getSqids()->encode($value),
            integerId: $value
        );
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Sqid) {
            return $value->integerId();
        }

        if (method_exists($value, 'getId')) {
            return $value->getId()->integerId();
        }
        
        return $this->getSqids()->decode($value);
    }

    private function getSqids(): SqidService
    {
        if (!$this->sqids) {
            $this->sqids = new SqidService();
        }

        return $this->sqids;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

}
