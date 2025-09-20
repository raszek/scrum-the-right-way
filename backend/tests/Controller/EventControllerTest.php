<?php

namespace App\Tests\Controller;


use App\Event\Thread\ThreadEventList;
use App\Factory\Event\EventFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Thread\ThreadFactory;
use App\Factory\UserFactory;
use Carbon\CarbonImmutable;

class EventControllerTest extends WebTestCase
{

    /** @test */
    public function project_member_can_see_project_thread_activities()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'firstName' => 'Bob',
            'lastName' => 'Smith'
        ]);

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create();

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project
        ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $threadOne = ThreadFactory::createOne([
            'title' => 'first thread',
            'project' => $project
        ]);

        $threadTwo = ThreadFactory::createOne([
            'title' => 'second thread',
            'project' => $project
        ]);

        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CREATE,
            'project' => $project,
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 10, 10, 10),
            'params' => [
                'threadId' => $threadOne->getId()->integerId(),
            ]
        ]);

        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CREATE,
            'project' => $project,
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2011, 11, 11, 11, 11, 11),
            'params' => [
                'threadId' => $threadTwo->getId()->integerId(),
            ]
        ]);

        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CLOSE,
            'project' => $project,
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12, 12),
            'params' => [
                'threadId' => $threadOne->getId()->integerId(),
            ]
        ]);

        $this->loginAsUser($user);

        $this->goToPage(
            sprintf(
                '/projects/%s/activities',
                $project->getId()
            )
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasText('Bob Smith has created thread first thread');
        $this->assertResponseHasText('Bob Smith has created thread second thread');
        $this->assertResponseHasText('Bob Smith has closed thread first thread');
    }

    /** @test */
    public function events_can_be_filtered_by_type()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne([
            'firstName' => 'Bob',
            'lastName' => 'Smith'
        ]);

        $project = ProjectFactory::new()
            ->withScrumType()
            ->create();

        SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project
        ]);

        ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $threadOne = ThreadFactory::createOne([
            'title' => 'first thread',
            'project' => $project
        ]);

        $threadTwo = ThreadFactory::createOne([
            'title' => 'second thread',
            'project' => $project
        ]);

        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CREATE,
            'project' => $project,
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 10, 10, 10),
            'params' => [
                'threadId' => $threadOne->getId()->integerId(),
            ]
        ]);

        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CREATE,
            'project' => $project,
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2011, 11, 11, 11, 11, 11),
            'params' => [
                'threadId' => $threadTwo->getId()->integerId(),
            ]
        ]);

        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CLOSE,
            'project' => $project,
            'createdBy' => $user,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12, 12),
            'params' => [
                'threadId' => $threadOne->getId()->integerId(),
            ]
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPageSafe(
            sprintf(
                '/projects/%s/activities',
                $project->getId()
            )
        );

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Search')->form();

        $client->submit($form, [
            'search_event[name]' => 'THREAD_CLOSE',
        ]);

        $this->assertResponseIsSuccessful();
        
        $table = $this->readTable('table');

        $this->assertEquals('Bob Smith has closed thread first thread', $table[1][0]);
        $this->assertEquals('12.12.2012 12:12', $table[1][1]);
    }
}
