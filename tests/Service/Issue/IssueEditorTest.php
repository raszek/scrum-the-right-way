<?php

namespace App\Tests\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Exception\Issue\CannotSetStoryPointsException;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;
use App\Service\Issue\IssueEditor;
use App\Service\Issue\IssueEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class IssueEditorTest extends KernelTestCase
{



    /** @test */
    public function editor_can_change_issue_at_row_number_position()
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issues = [];
        foreach (range(1, 5) as $i) {
            $issues[] = IssueFactory::createOne([
                'number' => $i,
                'columnOrder' => $i * 1024,
                'project' => $project,
                'issueColumn' => $backlogColumn
            ]);
        }

        $issueToBeMoved = $this->getIssueEditor($issues[4]->_real(), $user->_real());

        $issueToBeMoved->sort(2);

        $updatedIssues = $this->issueRepository()->backlogQuery($project)->getQuery()->getResult();

        $numbers = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getNumber());
        $orders = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getColumnOrder());

        $this->assertEquals([1, 5, 2, 3 ,4], $numbers);
        $this->assertEquals([1024, 1536, 2048, 3072, 4096], $orders);
    }

    /** @test */
    public function issues_will_be_defragmented_if_their_order_get_too_close()
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $backlogColumn = IssueColumnFactory::backlogColumn();

        IssueFactory::createOne([
            'number' => 1,
            'columnOrder' => 1,
            'project' => $project,
            'issueColumn' => $backlogColumn
        ]);

        $issueToBeMoved = IssueFactory::createOne([
            'number' => 2,
            'columnOrder' => 2048,
            'project' => $project,
            'issueColumn' => $backlogColumn
        ]);

        $issueEditor = $this->getIssueEditor($issueToBeMoved->_real(), $user->_real());

        $issueEditor->sort(1);

        $updatedIssues = $this->issueRepository()->backlogQuery($project)->getQuery()->getResult();

        $numbers = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getNumber());
        $orders = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getColumnOrder());

        $this->assertEquals([2, 1], $numbers);
        $this->assertEquals([512, 1024], $orders);
    }

    /** @test */
    public function issue_can_be_sorted_on_last_position_in_column()
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issues = [];
        foreach (range(1, 5) as $i) {
            $issues[] = IssueFactory::createOne([
                'number' => $i,
                'columnOrder' => $i * 1024,
                'project' => $project,
                'issueColumn' => $backlogColumn
            ]);
        }

        $issueToBeMoved = $this->getIssueEditor($issues[0]->_real(), $user->_real());

        $issueToBeMoved->sort(5);

        $updatedIssues = $this->issueRepository()->backlogQuery($project)->getQuery()->getResult();

        $numbers = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getNumber());
        $orders = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getColumnOrder());

        $this->assertEquals([2, 3, 4, 5, 1], $numbers);
        $this->assertEquals([2048, 3072, 4096, 5120, 6144], $orders);
    }

    /** @test */
    public function issue_can_be_moved_one_position_below()
    {
        self::bootKernel();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne();

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issues = [];
        foreach (range(1, 5) as $i) {
            $issues[] = IssueFactory::createOne([
                'number' => $i,
                'columnOrder' => $i * 1024,
                'project' => $project,
                'issueColumn' => $backlogColumn
            ]);
        }

        $issueToBeMoved = $this->getIssueEditor($issues[0]->_real(), $user->_real());

        $issueToBeMoved->sort(2);

        $updatedIssues = $this->issueRepository()->backlogQuery($project)->getQuery()->getResult();

        $numbers = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getNumber());
        $orders = ArrayHelper::map($updatedIssues, fn(Issue $issue) => $issue->getColumnOrder());

        $this->assertEquals([2, 1, 3, 4, 5], $numbers);
        $this->assertEquals([2048, 2560, 3072, 4096, 5120], $orders);
    }

    /** @test */
    public function story_point_must_be_bigger_than_0()
    {
        $user = UserFactory::createOne();

        $issue = IssueFactory::createOne();

        $issueEditor = $this->getIssueEditor($issue->_real(), $user->_real());

        $errorMessage = null;
        try {
            $issueEditor->setStoryPoints(0);
        } catch (CannotSetStoryPointsException $e) {
            $errorMessage = $e->getMessage();
        }

        $this->assertNotNull($errorMessage);
        $this->assertEquals('Story points value must be bigger than 0', $errorMessage);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

    private function getIssueEditor(Issue $issue, User $user): IssueEditor
    {
        return $this->getService(IssueEditorFactory::class)->create($issue, $user);
    }
}
