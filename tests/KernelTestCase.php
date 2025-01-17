<?php

namespace App\Tests;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    /**
     * @template T
     * @param class-string<T> $className
     * @return T
     */
    public function getService(string $className): mixed
    {
        return self::getContainer()->get($className);
    }

    public function mockService(string $className, mixed $mockObject): void
    {
        self::getContainer()->set($className, $mockObject);
    }
}
