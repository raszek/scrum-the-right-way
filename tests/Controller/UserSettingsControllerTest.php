<?php

namespace App\Tests\Controller;

use App\Factory\UserFactory;
use App\Service\Issue\Session\IssueSessionSettings;

class UserSettingsControllerTest extends WebTestCase
{

    /** @test */
    public function user_can_change_visibility_of_activities()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $this->loginAsUser($user);

        $client->request('POST', '/user-settings/activities-visible', [
            'state' => 'true',
        ]);

        $this->assertResponseStatusCodeSame(204);

        $requestAfterResponse = $client->getRequest();

        $this->assertEquals('true', $requestAfterResponse->getSession()->get(IssueSessionSettings::ACTIVITIES_VISIBLE_KEY));
    }

}
