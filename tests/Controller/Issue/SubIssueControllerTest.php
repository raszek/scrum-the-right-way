<?php

namespace App\Tests\Controller\Issue;

use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Helper\JsonHelper;
use App\Repository\Issue\IssueRepository;
use App\Tests\Controller\WebTestCase;

class SubIssueControllerTest extends WebTestCase
{

    /** @test */
    public function developer_can_create_sub_issues()
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

        $featureType = IssueTypeFactory::featureType();

        IssueTypeFactory::subIssueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $featureType,
            'number' => 12,
        ]);

        $this->loginAsUser($developer);

        $uri = sprintf(
            '/projects/%s/issues/SCP-12/sub-issues',
            $project->getId(),
        );

        $client->request('POST', $uri, [
            'title' => 'Some issue subtitle'
        ]);

        $this->assertResponseStatusCodeSame(201);

        $updatedIssue = $this->issueRepository()->findOneBy([
            'id' => $issue->getId(),
        ]);

        $createdSubIssue = $updatedIssue->getSubIssues()->get(0);

        $this->assertNotNull($createdSubIssue);
        $this->assertEquals('Some issue subtitle', $createdSubIssue->getTitle());
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
