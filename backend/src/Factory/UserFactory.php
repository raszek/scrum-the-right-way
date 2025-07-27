<?php

namespace App\Factory;

use App\Entity\User\User;
use App\Enum\User\UserRoleEnum;
use App\Enum\User\UserStatusEnum;
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

    public function withActiveStatus(): static
    {
        return $this->with(['statusId' => UserStatusEnum::Active]);
    }

    public function withNotActiveStatus(): static
    {
        return $this->with(['statusId' => UserStatusEnum::InActive]);
    }

    protected function defaults(): array|callable
    {
        $createdAt = DateTimeImmutable::createFromMutable(self::faker()->dateTime());

        return [
            'email' => self::faker()->email(),
            'plainPassword' => self::faker()->password(),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'createdAt' => $createdAt,
            'statusId' => UserStatusEnum::Active,
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
