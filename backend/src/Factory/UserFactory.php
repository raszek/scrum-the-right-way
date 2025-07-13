<?php

namespace App\Factory;

use App\Entity\User\User;
use App\Enum\User\UserRoleEnum;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    public function withAdminRole(): static
    {
        return $this->with(['roles' => [UserRoleEnum::Admin->value]]);
    }

    protected function defaults(): array|callable
    {
        return [
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'email' => self::faker()->email(),
            'plainPassword' => self::faker()->password(),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user) {
                if ($user->getPlainPassword()) {
                    $user->setPasswordHash($this->userPasswordHasher->hashPassword($user, $user->getPlainPassword()));
                }
            })
        ;
    }
}
