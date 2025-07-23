<?php

namespace App\Tests\Controller\User;

use App\Factory\UserFactory;
use App\Tests\Controller\WebTestCase;

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

        $this->assertEquals('John', $user->getFirstName());;
        $this->assertEquals('Terry', $user->getLastName());;
    }
}
