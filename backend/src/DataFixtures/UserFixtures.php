<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    const PROJECT_ADMIN_EMAIL = 'projectadmin@example.com';

    const CLIENT_EMAIL = 'client@example.com';

    const ANALYTIC_EMAIL = 'analytic@example.com';

    const DEVELOPER_EMAIL = 'developer@example.com';

    const TESTER_EMAIL = 'tester@example.com';

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

        UserFactory::createMany(15, [
            'plainPassword' => 'Password123!',
        ]);
    }
}
