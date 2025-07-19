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
            'user_form[email]' => 'raszek@wp.pl',
            'user_form[firstName]' => 'Donald',
            'user_form[lastName]' => 'Smith',
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
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => 'raszek@wp.pl'
            ]);

        $this->loginAsUser($admin);

        $crawler = $this->goToPageSafe('/admin/users/create');

        $form = $crawler->selectButton('Create')->form();

        $client->submit($form, [
            'user_form[email]' => 'raszek@wp.pl',
            'user_form[firstName]' => 'Donald',
            'user_form[lastName]' => 'Smith',
        ]);

        $this->assertResponseStatusCodeSame(422);

        $userCount = $this->userRepository()->count();

        $this->assertEquals(1, $userCount);

        $this->assertResponseHasText('Email is already in use');
    }

    /** @test */
    public function admin_can_update_user()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => 'admin@wp.pl',
                'firstName' => 'Admin',
                'lastName' => 'Admin'
            ]);

        $this->loginAsUser($admin);

        $url = sprintf('/admin/users/%s/edit', $admin->getId());

        $crawler = $this->goToPageSafe($url);

        $form = $crawler->selectButton('Update')->form();

        $client->submit($form, [
            'user_form[email]' => 'newemail@wp.pl',
            'user_form[firstName]' => 'Donald',
            'user_form[lastName]' => 'Smith',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertPath($url);

        $this->assertResponseHasText('User successfully updated.');

        $this->assertEquals('newemail@wp.pl', $admin->getEmail());
        $this->assertEquals('Donald', $admin->getFirstName());
        $this->assertEquals('Smith', $admin->getLastName());
    }

    /** @test */
    public function admin_can_update_first_name_only()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => 'admin@wp.pl',
                'firstName' => 'Admin',
                'lastName' => 'Smith'
            ]);

        $this->loginAsUser($admin);

        $url = sprintf('/admin/users/%s/edit', $admin->getId());

        $crawler = $this->goToPageSafe($url);

        $form = $crawler->selectButton('Update')->form();

        $client->submit($form, [
            'user_form[email]' => 'admin@wp.pl',
            'user_form[firstName]' => 'Donald',
            'user_form[lastName]' => 'Smith',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertPath($url);

        $this->assertResponseHasText('User successfully updated.');

        $this->assertEquals('admin@wp.pl', $admin->getEmail());
        $this->assertEquals('Donald', $admin->getFirstName());
        $this->assertEquals('Smith', $admin->getLastName());
    }

    /** @test */
    public function admin_can_deactivate_user()
    {
        $client = static::createClient();
        $client->followRedirects();

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => 'admin@wp.pl',
                'firstName' => 'Admin',
                'lastName' => 'Admin'
            ]);

        $user = UserFactory::createOne([
            'plainPassword' => 'Password123!',
            'activationCode' => 'some-activation-code'
        ]);

        $this->loginAsUser($admin);

        $url = sprintf('/admin/users/%s/deactivate', $user->getId());

        $client->request('POST', $url);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('User has been deactivated.');

        $this->assertNull($user->getActivationCode());
        $this->assertNull($user->getPasswordHash());
    }

    private function userRepository(): UserRepository
    {
        return $this->getService(UserRepository::class);
    }
}
