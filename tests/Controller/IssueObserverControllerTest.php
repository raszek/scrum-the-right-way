<?php

namespace App\Tests\Controller;

use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueObserverFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\UserFactory;
use App\Repository\Issue\IssueObserverRepository;
use Zenstruck\Foundry\Test\Factories;

class IssueObserverControllerTest extends WebTestCase
{



    /** @test */
    public function project_member_can_observe_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $projectMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/observe');

        $this->assertResponseStatusCodeSame(204);

        $observers = $this->issueObserverRepository()->findBy([
            'issue' => $issue->getId()
        ]);

        $this->assertCount(1, $observers);

        $this->assertEquals($projectMember->getId(), $observers[0]->getProjectMember()->getId());
    }

    /** @test */
    public function project_member_can_unobserve_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $projectMember = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        IssueObserverFactory::createOne([
            'issue' => $issue,
            'projectMember' => $projectMember,
        ]);

        $this->loginAsUser($user);

        $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/unobserve');

        $this->assertResponseStatusCodeSame(204);

        $observers = $this->issueObserverRepository()->findBy([
            'issue' => $issue->getId()
        ]);

        $this->assertCount(0, $observers);
    }

    private function issueObserverRepository(): IssueObserverRepository
    {
        return $this->getService(IssueObserverRepository::class);
    }
}
