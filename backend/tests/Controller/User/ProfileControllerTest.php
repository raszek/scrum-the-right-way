<?php

namespace App\Tests\Controller\User;

use App\Factory\UserFactory;
use App\Tests\Controller\WebTestCase;
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

    private function userPasswordHasher(): UserPasswordHasherInterface
    {
        return $this->getService(UserPasswordHasherInterface::class);
    }
}
