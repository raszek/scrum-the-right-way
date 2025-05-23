<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\Factories;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{

    use Factories;

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

    public function entityManager(): EntityManagerInterface
    {
        return $this->getService(EntityManagerInterface::class);
    }
}
