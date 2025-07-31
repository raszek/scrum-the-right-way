<?php

namespace App\Tests\Controller\User;

use App\Enum\User\UserCodeTypeEnum;
use App\Factory\User\UserCodeFactory;
use App\Factory\UserFactory;
use App\Repository\User\UserCodeRepository;
use App\Tests\Controller\WebTestCase;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileControllerTest extends WebTestCase
{

    /** @test */
    public function user_can_update_his_first_name_and_last_name()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'firstName' => 'Bob',
            'lastName' => 'Smith'
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe('/profile');

        $form = $crawler->selectButton('Save')->form();

        $client->submit($form, [
            'profile_form[firstName]' => 'John',
            'profile_form[lastName]' => 'Terry',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Profile successfully updated.');

        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Terry', $user->getLastName());
    }

    /** @test */
    public function on_validation_error_form_has_submitted_value()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'firstName' => 'Bob',
            'lastName' => 'Smith'
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe('/profile');

        $form = $crawler->selectButton('Save')->form();

        $submitCrawler = $client->submit($form, [
            'profile_form[firstName]' => 'John',
            'profile_form[lastName]' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);

        $this->assertResponseHasText('Last name cannot be blank');

        $submittedForm = $submitCrawler->selectButton('Save')->form();

        $this->assertEquals('', $submittedForm->get('profile_form[lastName]')->getValue());
    }

    /** @test */
    public function user_can_change_password()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'firstName' => 'Bob',
            'lastName' => 'Smith',
            'plainPassword' => 'some-password'
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe('/profile/change-password');

        $form = $crawler->selectButton('Save')->form();

        $client->submit($form, [
            'change_password_form[currentPassword]' => 'some-password',
            'change_password_form[newPassword]' => 'Password123!',
            'change_password_form[repeatPassword]' => 'Password123!',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Password successfully changed.');

        $this->assertTrue($this->userPasswordHasher()->isPasswordValid($user, 'Password123!'));
    }

    /** @test */
    public function user_can_change_his_email()
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
            'email' => 'donek@example.com',
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe('/profile/change-email');

        $form = $crawler->selectButton('Change email')->form();

        $client->submit($form, [
            'change_email_form[email]' => 'zenek@example.com',
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Email sent was to your inbox. Confirm changing your email address.');
        $this->assertTrue($mailerMock->isMailSent);

        $changeEmailCode = $this->userCodeRepository()->findOneBy([
            'mainUser' => $user,
            'type' => UserCodeTypeEnum::ChangeEmail->value
        ]);

        $this->assertNotNull($changeEmailCode);
        $this->assertEquals([
            'email' => 'zenek@example.com',
        ], $changeEmailCode->getData());
    }

    /** @test */
    public function user_can_confirm_change_of_his_email()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'email' => 'donek@example.com',
        ]);

        UserCodeFactory::createOne([
            'mainUser' => $user,
            'type' => UserCodeTypeEnum::ChangeEmail,
            'code' => 'some-code',
            'data' => [
                'email' => 'zenek@example.com'
            ],
            'createdAt' => CarbonImmutable::now()->subMinutes(30)
        ]);

        $this->loginAsUser($user);

        $url = sprintf('/profile/confirm-change-email/some-code');

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Email successfully changed.');

        $this->assertEquals('zenek@example.com', $user->getEmail());
    }

    /** @test */
    public function user_can_set_his_avatar()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $fileName = 'cat.jpg';
        $imagePath = $this->temporaryFromDataFile($fileName);

        $uploadedFile = new UploadedFile($imagePath, $fileName);

        $this->loginAsUser($user);

        $client->request('POST', 'profile/avatar', files: [
            'avatar' => $uploadedFile
        ]);

        $this->assertResponseIsSuccessful();

        $profile = $user->getProfile();

        $this->assertNotNull($profile->getAvatar());
        $this->assertNotNull($profile->getAvatarThumb());

        $this->assertEquals('cat.jpg', $profile->getAvatar()->getName());
        $this->assertEquals('cat_thumb.jpg', $profile->getAvatarThumb()->getName());
    }

    protected function tearDown(): void
    {
        $this->cleanUploadDirectory();

        parent::tearDown();
    }

    private function userPasswordHasher(): UserPasswordHasherInterface
    {
        return $this->getService(UserPasswordHasherInterface::class);
    }

    private function userCodeRepository(): UserCodeRepository
    {
        return $this->getService(UserCodeRepository::class);
    }
}
