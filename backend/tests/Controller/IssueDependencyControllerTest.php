<?php

namespace App\Tests\Controller;

use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueDependencyFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Helper\JsonHelper;
use App\Repository\Issue\IssueRepository;
use Zenstruck\Foundry\Test\Factories;

class IssueDependencyControllerTest extends WebTestCase
{



    /** @test */
    public function developer_can_add_dependency_to_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 5,
            'title' => 'Another issue title'
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/dependencies/SCP-5/add',
            $project->getId(),
        );

        $client->request('POST', $uri);

        $this->assertResponseStatusCodeSame(201);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertCount(1, $updatedIssue->getIssueDependencies());

        $addedDependency = $updatedIssue->getIssueDependencies()->get(0);

        $this->assertEquals('Another issue title', $addedDependency->getDependency()->getTitle());
    }

    /** @test */
    public function developer_can_remove_dependency_from_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $anotherIssue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 5,
            'title' => 'Another issue title'
        ]);

        IssueDependencyFactory::createOne([
            'issue' => $issue,
            'dependency' => $anotherIssue
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/dependencies/SCP-5/remove',
            $project->getId(),
        );

        $client->request('POST', $uri);

        $this->assertResponseStatusCodeSame(204);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId()
        ]);

        $this->assertCount(0, $updatedIssue->getIssueDependencies());
    }

    /** @test */
    public function developer_can_list_issue_dependencies()
    {
        $client = static::createClient();
        $client->followRedirects();

        $developer = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $developer,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 5,
            'title' => 'issue five'
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 7,
            'title' => 'issue seven'
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/dependencies?%s',
            $project->getId(),
            http_build_query([
                'search' => '#5'
            ])
        );

        $client->request('GET', $uri);

        $this->assertResponseStatusCodeSame(200);

        $result = JsonHelper::decode($client->getResponse()->getContent());

        $this->assertEquals([
            [
                'value' => 'SCP-5',
                'text' => '[#5] issue five',
                'url' => sprintf('/projects/%s/issues/SCP-5', $project->getId()),
                'addUrl' => sprintf('/projects/%s/issues/SCP-12/dependencies/SCP-5/add', $project->getId()),
                'removeUrl' => sprintf('/projects/%s/issues/SCP-12/dependencies/SCP-5/remove', $project->getId()),
            ]
        ], $result);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

}
