<?php

namespace App\Tests\Controller\Issue;

use App\Entity\Issue\Issue;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Helper\ArrayHelper;
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

    /** @test */
    public function developer_can_reorder_sub_issue()
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

        $issueType = IssueTypeFactory::issueType();

        $feature = IssueFactory::createOne([
            'number' => 1,
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $featureType,
        ]);

        foreach (range(2, 6) as $i) {
            IssueFactory::createOne([
                'number' => $i,
                'project' => $project,
                'issueColumn' => $backlogColumn,
                'issueOrder' => ($i - 1) * 1024,
                'type' => $issueType,
                'parent' => $feature
            ]);
        }

        $uri = sprintf(
            '/projects/%s/sub-issues/SCP-5/sort',
            $project->getId()
        );

        $this->loginAsUser($developer);

        $client->request('POST', $uri, [
            'position' => 2,
        ]);

        $this->assertResponseStatusCodeSame(204);

        $updatedSubIssues = $this->issueRepository()->featureSubIssues($feature);

        $numbers = ArrayHelper::map($updatedSubIssues, fn(Issue $issue) => $issue->getNumber());
        $orders = ArrayHelper::map($updatedSubIssues, fn(Issue $issue) => $issue->getIssueOrder());

        $this->assertEquals([2, 5, 3, 4, 6], $numbers);
        $this->assertEquals([1024, 1536, 2048, 3072, 5120], $orders);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
