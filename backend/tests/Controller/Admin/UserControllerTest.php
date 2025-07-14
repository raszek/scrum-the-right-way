<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User\User;
use App\Factory\UserFactory;
use App\Repository\User\UserRepository;
use App\Service\Site\CreateUserEmail;
use App\Tests\Controller\WebTestCase;

class UserControllerTest extends WebTestCase
{

    /** @test */
    public function admin_can_list_all_users()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create([
                'firstName' => 'Admin',
                'lastName' => 'Admin'
            ]);

        UserFactory::createOne([
            'firstName' => 'User',
            'lastName' => 'User'
        ]);

        $this->loginAsUser($admin);

        $this->goToPage('/admin/users');

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Admin Admin');
        $this->assertResponseHasText('User User');
    }

    /** @test */
    public function admin_can_create_user()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create();

        $registerMailMock = new class extends CreateUserEmail
        {
            public function __construct()
            {
            }

            public bool $isMailSent = false;

            public function send(User $user): void
            {
                $this->isMailSent = true;
            }
        };

        $this->mockService(CreateUserEmail::class, $registerMailMock);

        $this->loginAsUser($admin);

        $crawler = $this->goToPageSafe('/admin/users/create');

        $form = $crawler->selectButton('Create')->form();

        $client->submit($form, [
            'create_user[email]' => 'raszek@wp.pl',
            'create_user[firstName]' => 'Donald',
            'create_user[lastName]' => 'Smith',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('User successfully created. Welcome email was sent to user.');

        $createdUser = $this->userRepository()->findOneBy([
            'email' => 'raszek@wp.pl'
        ]);

        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->getActivationCode());
        $this->assertTrue($registerMailMock->isMailSent);

        $client->enableReboot();
    }

    /** @test */
    public function admin_cannot_create_account_with_existing_email()
    {
        $this->markTestSkipped();

        $client = static::createClient();
        $client->followRedirects();

        UserFactory::createOne([
            'email' => 'raszek@wp.pl',
        ]);

        $crawler = $this->goToPageSafe('/register');

        $form = $crawler->selectButton('Register')->form();

        $client->submit($form, [
            'register[email]' => 'raszek@wp.pl',
            'register[password][first]' => 'Password123!',
            'register[password][second]' => 'Password123!',
        ]);

        $this->assertResponseStatusCodeSame(422);

        $userCount = $this->userRepository()->count();

        $this->assertEquals(1, $userCount);

        $this->assertResponseHasText('This value is already used.');
    }

    private function userRepository(): UserRepository
    {
        return $this->getService(UserRepository::class);
    }
}
