<?php

namespace App\Tests\Controller;

use App\Entity\User\User;
use App\Factory\UserFactory;
use App\Repository\User\UserRepository;
use App\Service\Site\RegisterMail;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;

class SiteControllerTest extends WebTestCase
{

    use Factories;

    /** @test */
    public function user_can_log_out()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/logout');

        $this->assertResponseIsSuccessful();

        $this->assertPath('/');
    }

    /** @test */
    public function user_can_reset_password()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
            'resetPasswordCode' => 'some-reset-password-code'
        ]);

        $crawler = $this->goToPageSafe('/reset-password/test@test.com/some-reset-password-code');

        $form = $crawler->selectButton('Reset')->form();

        $client->submit($form, [
            'reset_password[password][first]' => 'NewPass123!',
            'reset_password[password][second]' => 'NewPass123!',
            'reset_password[email]' => 'test@test.com',
            'reset_password[resetPasswordCode]' => 'some-reset-password-code'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertPath('/login');

        $this->assertResponseHasText('Successfully reset password!');

        $updatedUser = $this->userRepository()->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertNotNull($updatedUser);
        $this->assertNull($updatedUser->getResetPasswordCode());

        $isPasswordValid = $this->getUserPasswordHasher()->isPasswordValid($updatedUser, 'NewPass123!');

        $this->assertTrue($isPasswordValid);
    }

    /** @test */
    public function user_can_enter_email_to_send_reset_password_email()
    {
        $client = static::createClient();
        $client->followRedirects();

        UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
            'activationCode' => 'some-activation-code'
        ]);

        $crawler = $this->goToPageSafe('/forgot-password');

        $form = $crawler->selectButton('Send')->form();

        $client->submit($form, [
            'forgot_password[email]' => 'test@test.com',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Email was sent to your inbox if your account exist');

        $updatedUser = $this->userRepository()->findOneBy([
            'email' => 'test@test.com'
        ]);

        $this->assertNotNull($updatedUser);
        $this->assertNotNull($updatedUser->getResetPasswordCode());
    }

    /** @test */
    public function user_cannot_log_in_if_his_account_is_inactive()
    {
        $client = static::createClient();
        $client->followRedirects();

        UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
            'activationCode' => 'some-activation-code'
        ]);

        $crawler = $this->goToPageSafe('/login');

        $form = $crawler->selectButton('Login')->form();

        $client->submit($form, [
            '_username' => 'test@test.com',
            '_password' => 'Password123!',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertPath('/login');

        $this->assertResponseHasText('Your account is inactive');
    }

    /** @test */
    public function user_can_log_in_to_his_account()
    {
        $client = static::createClient();
        $client->followRedirects();

        UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
            'activationCode' => null
        ]);

        $crawler = $this->goToPageSafe('/login');

        $form = $crawler->selectButton('Login')->form();

        $client->submit($form, [
            '_username' => 'test@test.com',
            '_password' => 'Password123!',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertPath('/app/home');
    }

    /** @test */
    public function user_can_activate_his_account()
    {
        $client = static::createClient();
        $client->followRedirects();

        UserFactory::createOne([
            'email' => 'test@test.com',
            'activationCode' => 'some-activation-code'
        ]);

        $this->goToPage('/activate-account/test@test.com/some-activation-code');

        $this->assertResponseIsSuccessful();

        $this->assertPath('/login');

        $this->assertResponseHasText('Account successfully activated');
    }

    /** @test */
    public function user_can_register_account()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $registerMailMock = new class extends RegisterMail
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

        $this->mockService(RegisterMail::class, $registerMailMock);

        $crawler = $this->goToPageSafe('/register');

        $form = $crawler->selectButton('Register')->form();

        $client->submit($form, [
            'register[email]' => 'raszek@wp.pl',
            'register[firstName]' => 'Donald',
            'register[lastName]' => 'Smith',
            'register[password][first]' => 'Password123!',
            'register[password][second]' => 'Password123!',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('You successfully created account. Confirmation mail was sent to your email address.');

        $createdUser = $this->userRepository()->findOneBy([
            'email' => 'raszek@wp.pl'
        ]);

        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->getActivationCode());
        $this->assertTrue($registerMailMock->isMailSent);

        $client->enableReboot();
    }

    /** @test */
    public function user_cannot_register_account_with_existing_email()
    {
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

    private function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        return $this->getService(UserPasswordHasherInterface::class);
    }
}
