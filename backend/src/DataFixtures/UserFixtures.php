<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    const string PROJECT_ADMIN_EMAIL = 'projectadmin@example.com';

    const string CLIENT_EMAIL = 'client@example.com';

    const string ANALYTIC_EMAIL = 'analytic@example.com';

    const string DEVELOPER_EMAIL = 'developer@example.com';

    const string TESTER_EMAIL = 'tester@example.com';

    /**
     * @return string[]
     */
    public static function userEmails(): array
    {
        return [
            self::PROJECT_ADMIN_EMAIL,
            self::CLIENT_EMAIL,
            self::ANALYTIC_EMAIL,
            self::DEVELOPER_EMAIL,
            self::TESTER_EMAIL,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::userEmails() as $userEmail) {
            UserFactory::createOne([
                'email' => $userEmail,
                'plainPassword' => 'Password123!',
            ]);
        }

        UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => 'admin@example.com',
                'plainPassword' => 'Password123!'
            ]);

        UserFactory::createMany(15, [
            'plainPassword' => 'Password123!',
        ]);
    }
}
