<?php

namespace App\Tests\Controller;

use App\Factory\Issue\DescriptionHistoryFactory;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\UserFactory;
use App\Helper\JsonHelper;
use Carbon\CarbonImmutable;
use Symfony\Component\DomCrawler\Crawler;

class DescriptionHistoryControllerTest extends WebTestCase
{

    /** @test */
    public function project_member_can_list_issue_description_changes()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        ProjectMemberFactory::createOne([
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
            'description' => null
        ]);

        SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true
        ]);

        DescriptionHistoryFactory::createOne([
            'issue' => $issue,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12),
        ]);

        DescriptionHistoryFactory::createOne([
            'issue' => $issue,
            'createdAt' => CarbonImmutable::create(2010, 10, 10, 10, 10),
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPage(sprintf(
            '/projects/%s/issues/%s/description-history',
            $project->getId(),
            $issue->getCode(),
        ));

        $this->assertResponseIsSuccessful();

        $results = $crawler->filter('.list-group-item')->each(function (Crawler $item) {
            return $item->text();
        });

        $expectations = [
            'December 12, 2012 12:12',
            'October 10, 2010 10:10',
        ];

        $this->assertEquals($expectations, $results);
    }

    /** @test */
    public function project_member_can_view_issue_description_changes()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        ProjectMemberFactory::createOne([
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
            'description' => null
        ]);

        $changes = DescriptionHistoryFactory::createOne([
            'issue' => $issue,
            'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12),
            'changes' => $this->exampleChanges()
        ]);

        $this->loginAsUser($user);

        $crawler = $this->goToPage(sprintf(
            '/projects/%s/issues/%s/description-history/%s',
            $project->getId(),
            $issue->getCode(),
            $changes->getId()
        ));

        $this->assertResponseIsSuccessful();
        
        $table = $this->readTable('table');

        $this->assertEquals('## Working on something', $table[1][0]);
        $this->assertEquals('## Added new text', $table[3][0]);
        $this->assertEquals('## Super new text', $table[5][0]);
    }

    private function exampleChanges(): array
    {
        $json = <<<EOT
        [
          [
            {
              "tag": 1,
              "old": {
                "offset": 0,
                "lines": [
                  "## Working on something",
                  "",
                  "## Added new text"
                ]
              },
              "new": {
                "offset": 0,
                "lines": [
                  "## Working on something",
                  "",
                  "## Added new text"
                ]
              }
            },
            {
              "tag": 4,
              "old": {
                "offset": 3,
                "lines": [
                  ""
                ]
              },
              "new": {
                "offset": 3,
                "lines": [
                  "",
                  "## Super new text"
                ]
              }
            }
          ]
        ]
        EOT;

        return JsonHelper::decode($json);
    }
}
