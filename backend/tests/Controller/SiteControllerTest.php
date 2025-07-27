<?php

namespace App\Tests\Controller;

use App\Enum\User\UserCodeTypeEnum;
use App\Enum\User\UserStatusEnum;
use App\Factory\User\UserCodeFactory;
use App\Factory\UserFactory;
use App\Repository\User\UserCodeRepository;
use App\Repository\User\UserRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SiteControllerTest extends WebTestCase
{

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
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2010, 10, 10, 9, 10);
            }
        };

        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
        ]);

        $userCode = UserCodeFactory::createOne([
            'mainUser' => $user,
            'type' => UserCodeTypeEnum::ResetPassword,
            'code' => 'some-reset-password-code',
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 9)
        ]);

        $crawler = $this->goToPageSafe('/reset-password/test@test.com/some-reset-password-code');

        $form = $crawler->selectButton('Reset')->form();

        $client->submit($form, [
            'reset_password_form[password]' => 'NewPass123!',
            'reset_password_form[repeatPassword]' => 'NewPass123!',
            'reset_password_form[email]' => 'test@test.com',
            'reset_password_form[resetPasswordCode]' => 'some-reset-password-code',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertPath('/login');

        $this->assertResponseHasText('Successfully reset password!');

        $updatedUser = $this->userRepository()->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertNotNull($updatedUser);

        $isPasswordValid = $this->getUserPasswordHasher()->isPasswordValid($updatedUser, 'NewPass123!');

        $this->assertTrue($isPasswordValid);

        $this->assertNotNull($userCode->getUsedAt());
    }

    /** @test */
    public function user_reset_link_is_only_valid_for_1_hour()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2010, 10, 10, 10, 10);
            }
        };

        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
        ]);

        UserCodeFactory::createOne([
            'mainUser' => $user,
            'type' => UserCodeTypeEnum::ResetPassword,
            'code' => 'some-reset-password-code',
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 9)
        ]);

        $crawler = $this->goToPageSafe('/reset-password/test@test.com/some-reset-password-code');

        $form = $crawler->selectButton('Reset')->form();

        $client->submit($form, [
            'reset_password_form[password]' => 'NewPass123!',
            'reset_password_form[repeatPassword]' => 'NewPass123!',
            'reset_password_form[email]' => 'test@test.com',
            'reset_password_form[resetPasswordCode]' => 'some-reset-password-code',
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->assertResponseHasText('Code expired');
    }

    /** @test */
    public function user_can_enter_email_to_send_reset_password_email()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $mailerMock = new class implements MailerInterface {

            public bool $isMailSent = false;

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                $this->isMailSent = true;
            }
        };
        $this->mockService(MailerInterface::class, $mailerMock);

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
        ]);

        $crawler = $this->goToPageSafe('/forgot-password');

        $form = $crawler->selectButton('Send')->form();

        $client->submit($form, [
            'forgot_password_form[email]' => 'test@test.com',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Email was sent to your inbox if your account exist');

        $userCode = $this->userCodeRepository()->findOneBy([
            'mainUser' => $user,
            'type' => UserCodeTypeEnum::ResetPassword->value
        ]);

        $this->assertNotNull($userCode);
        $this->assertNull($userCode->getUsedAt());
        $this->assertTrue($mailerMock->isMailSent);;
    }

    /** @test */
    public function user_cannot_log_in_if_his_account_is_inactive()
    {
        $client = static::createClient();
        $client->followRedirects();

        UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => 'Password123!',
            'statusId' => UserStatusEnum::InActive
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

        UserFactory::new()
            ->withActiveStatus()
            ->create([
                'email' => 'test@test.com',
                'plainPassword' => 'Password123!',
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
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2010, 10, 10, 10, 10);
            }
        };
        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => null,
        ]);

        $userCode = UserCodeFactory::createOne([
            'code' => 'some-activation-code',
            'type' => UserCodeTypeEnum::Activation,
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 10),
            'mainUser' => $user
        ]);

        $crawler = $this->goToPageSafe( '/activate-account/test@test.com/some-activation-code');

        $form = $crawler->selectButton('Reset')->form();

        $client->submit($form, [
            'reset_password_form[password]' => 'NewPass123!',
            'reset_password_form[repeatPassword]' => 'NewPass123!',
            'reset_password_form[email]' => 'test@test.com',
            'reset_password_form[resetPasswordCode]' => 'some-activation-code'
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertPath('/login');

        $this->assertResponseHasText('Account successfully activated. You can now log in.');

        $updatedUser = $this->userRepository()->findOneBy([
            'email' => 'test@test.com'
        ]);

        $this->assertNotNull($updatedUser);
        $this->assertTrue($updatedUser->isActive());

        $isPasswordValid = $this->getUserPasswordHasher()->isPasswordValid($updatedUser, 'NewPass123!');
        $this->assertTrue($isPasswordValid);

        $this->assertNotNull($userCode->getUsedAt());
    }

    /** @test */
    public function user_activation_link_is_only_valid_for_1_hour()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->disableReboot();

        $clockMock = new class implements ClockInterface {

            public function now(): CarbonImmutable
            {
                return CarbonImmutable::create(2010, 10, 10, 10, 10);
            }
        };
        $this->mockService(ClockInterface::class, $clockMock);

        $user = UserFactory::createOne([
            'email' => 'test@test.com',
            'plainPassword' => null,
        ]);

        UserCodeFactory::createOne([
            'code' => 'some-activation-code',
            'type' => UserCodeTypeEnum::Activation,
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 9),
            'mainUser' => $user,
        ]);

        $crawler = $this->goToPageSafe( '/activate-account/test@test.com/some-activation-code');

        $form = $crawler->selectButton('Reset')->form();

        $client->submit($form, [
            'reset_password_form[password]' => 'NewPass123!',
            'reset_password_form[repeatPassword]' => 'NewPass123!',
            'reset_password_form[email]' => 'test@test.com',
            'reset_password_form[resetPasswordCode]' => 'some-activation-code'
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->assertResponseHasText('Code expired');
    }

    private function userRepository(): UserRepository
    {
        return $this->getService(UserRepository::class);
    }

    private function userCodeRepository(): UserCodeRepository
    {
        return $this->getService(UserCodeRepository::class);
    }

    private function getUserPasswordHasher(): UserPasswordHasherInterface
    {
        return $this->getService(UserPasswordHasherInterface::class);
    }
}
