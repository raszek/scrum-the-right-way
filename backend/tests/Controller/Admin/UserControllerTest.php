<?php

namespace App\Tests\Controller\Admin;

use App\Email\Site\ActivationUserMessage;
use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Factory\UserFactory;
use App\Repository\User\UserCodeRepository;
use App\Repository\User\UserRepository;
use App\Service\Common\ClockInterface;
use App\Tests\Controller\WebTestCase;
use Carbon\CarbonImmutable;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

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

        $activationMailMock = new class extends ActivationUserMessage
        {
            public function __construct()
            {
            }

            public bool $isMailSent = false;

            public function send(UserCode $userCode): void
            {
                $this->isMailSent = true;
            }
        };

        $this->mockService(ActivationUserMessage::class, $activationMailMock);

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
        $this->assertTrue($activationMailMock->isMailSent);

        $createdUserCode = $this->userCodeRepository()->findOneBy([
            'mainUser' => $createdUser,
            'type' => UserCodeTypeEnum::Activation
        ]);

        $this->assertNotNull($createdUserCode);
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

        $this->assertEquals('admin@wp.pl', $form->get('user_form[email]')->getValue());
        $this->assertEquals('Admin', $form->get('user_form[firstName]')->getValue());
        $this->assertEquals('Admin', $form->get('user_form[lastName]')->getValue());

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

        $user = UserFactory::new()
            ->withActiveStatus()
            ->create([
                'plainPassword' => 'Password123!',
            ]);

        $this->loginAsUser($admin);

        $url = sprintf('/admin/users/%s/deactivate', $user->getId());

        $client->request('POST', $url);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('User has been deactivated.');

        $this->assertFalse($user->isActive());
        $this->assertNull($user->getPasswordHash());
    }

    /** @test */
    public function admin_can_send_activation_link()
    {
        $client = static::createClient();
        $client->followRedirects();

        $clockMock = new class implements ClockInterface {
            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2010, 10, 10);
            }
        };

        $this->mockService(ClockInterface::class, $clockMock);

        $mailerMock = new class implements MailerInterface {

            public bool $isMailSent = false;

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                $this->isMailSent = true;
            }
        };
        $this->mockService(MailerInterface::class, $mailerMock);

        $admin = UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => 'admin@wp.pl',
                'firstName' => 'Admin',
                'lastName' => 'Admin'
            ]);

        $user = UserFactory::new()
            ->withNotActiveStatus()
            ->create([
                'plainPassword' => 'Password123!',
            ]);

        $this->loginAsUser($admin);

        $url = sprintf('/admin/users/%s/send-activation-link', $user->getId());

        $client->request('POST', $url);

        $this->assertResponseIsSuccessful();

        $this->assertTrue($mailerMock->isMailSent);

        $createdUserCode = $this->userCodeRepository()->findOneBy([
            'mainUser' => $user,
            'type' => UserCodeTypeEnum::Activation,
        ]);

        $this->assertNotNull($createdUserCode);
        $this->assertEquals('2010-10-10', $createdUserCode->getCreatedAt()->format('Y-m-d'));
    }

    private function userRepository(): UserRepository
    {
        return $this->getService(UserRepository::class);
    }

    private function userCodeRepository(): UserCodeRepository
    {
        return $this->getService(UserCodeRepository::class);
    }
}
