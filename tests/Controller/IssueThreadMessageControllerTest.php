<?php

namespace App\Tests\Controller;

use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueThreadMessageFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\Thread\ThreadFactory;
use App\Factory\Thread\ThreadMessageFactory;
use App\Factory\UserFactory;
use App\Helper\JsonHelper;
use App\Repository\Issue\IssueRepository;
use Zenstruck\Foundry\Test\Factories;

class IssueThreadMessageControllerTest extends WebTestCase
{
    
    use Factories;

    /** @test */
    public function analytic_can_add_thread_message_to_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $thread = ThreadFactory::createOne([
            'project' => $project,
        ]);

        $threadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'content' => 'Some content'
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/messages/%s/add',
            $project->getId(),
            $threadMessage->getId()
        );

        $client->request('POST', $uri);

        $this->assertResponseStatusCodeSame(201);
        
        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertCount(1, $updatedIssue->getIssueThreadMessages());

        $addedMessage = $updatedIssue->getIssueThreadMessages()->get(0);

        $this->assertEquals('Some content', $addedMessage->getThreadMessage()->getContent());
    }

    /** @test */
    public function analytic_can_remove_thread_message_from_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $thread = ThreadFactory::createOne([
            'project' => $project,
        ]);

        $threadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'content' => 'Some content'
        ]);

        IssueThreadMessageFactory::createOne([
            'issue' => $issue,
            'threadMessage' => $threadMessage,
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/messages/%s/remove',
            $project->getId(),
            $threadMessage->getId()
        );

        $client->request('POST', $uri);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertCount(0, $updatedIssue->getIssueThreadMessages());
    }

    /** @test */
    public function analytic_can_search_thread_message_by_thread_title()
    {
        $client = static::createClient();
        $client->followRedirects();

        $analytic = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberAnalytic = ProjectMemberFactory::createOne([
            'user' => $analytic,
            'project' => $project
        ]);

        $analyticRole = ProjectRoleFactory::analyticRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberAnalytic,
            'role' => $analyticRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $thread = ThreadFactory::createOne([
            'project' => $project,
            'title' => 'Threadtitle',
            'slug' => 'threadtitle',
        ]);

        $threadMessage = ThreadMessageFactory::createOne([
            'thread' => $thread,
            'content' => 'Some content',
            'number' => 1
        ]);

        $this->loginAsUser($analytic);

        $uri = sprintf(
            '/projects/%s/issues/%s/messages?search=threadtitle',
            $project->getId(),
            $issue->getCode(),
        );

        $client->request('GET', $uri);

        $this->assertResponseIsSuccessful();

        $result = JsonHelper::decode($client->getResponse()->getContent());

        $url = sprintf(
            '/projects/%s/threads/%s/threadtitle/messages#1',
            $project->getId(),
            $thread->getId()
        );

        $addUrl = sprintf(
            '/projects/%s/issues/SCP-12/messages/%s/add',
            $project->getId(),
            $threadMessage->getId()
        );

        $removeUrl = sprintf(
            '/projects/%s/issues/SCP-12/messages/%s/remove',
            $project->getId(),
            $threadMessage->getId()
        );

        $this->assertEquals([
            [
                'value' => $threadMessage->getId()->get(),
                'text' => 'Threadtitle #1',
                'url' => $url,
                'addUrl' => $addUrl,
                'removeUrl' => $removeUrl,
            ]
        ], $result);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
