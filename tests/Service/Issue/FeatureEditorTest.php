<?php

namespace App\Tests\Service\Issue;


use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueRepository;
use App\Service\Issue\FeatureEditor;
use App\Service\Issue\FeatureEditorFactory;
use App\Tests\KernelTestCase;

class FeatureEditorTest extends KernelTestCase
{
    /** @test */
    public function when_there_is_no_space_left_for_first_sub_issue_every_feature_issue_should_be_reordered()
    {
        $user = UserFactory::createOne();

        $featureType = IssueTypeFactory::featureType();

        $subIssueType = IssueTypeFactory::subIssueType();

        $feature = IssueFactory::createOne([
            'type' => $featureType,
            'number' => 1,
        ]);

        IssueFactory::createOne([
            'type' => $subIssueType,
            'number' => 2,
            'parent' => $feature,
            'issueOrder' => 1
        ]);

        IssueFactory::createOne([
            'type' => $subIssueType,
            'number' => 3,
            'parent' => $feature,
            'issueOrder' => 64
        ]);

        $editor = $this->create($feature->_real(), $user->_real());

        $editor->add(new SubIssueForm(
            title: 'Some new title'
        ));

        $subIssues = $this->issueRepository()->findBy([
            'parent' => $feature->getId(),
        ], [
            'issueOrder' => 'ASC'
        ]);

        $this->assertEquals(512, $subIssues[0]->getIssueOrder());
        $this->assertEquals(1024, $subIssues[1]->getIssueOrder());
        $this->assertEquals(2048, $subIssues[2]->getIssueOrder());
    }

    /** @test */
    public function if_every_feature_sub_issue_is_to_do_then_feature_is_also_to_do()
    {
        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $toDoColumn = IssueColumnFactory::todoColumn();
        $inProgressColumn = IssueColumnFactory::inProgressColumn();

        $feature = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $inProgressColumn,
            'type' => IssueTypeFactory::featureType(),
            'number' => 1,
            'issueOrder' => 512
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'number' => 2,
            'issueOrder' => 1024,
            'type' => IssueTypeFactory::subIssueType(),
            'parent' => $feature
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'number' => 3,
            'issueOrder' => 1024,
            'type' => IssueTypeFactory::subIssueType(),
            'parent' => $feature
        ]);

        $realFeature = $feature->_real();

        $featureStrategy = $this->create($realFeature, $user->_real());;

        $featureStrategy->updateIssueColumn();

        $this->assertTrue($realFeature->getIssueColumn()->isToDo());
    }

    /** @test */
    public function if_at_least_one_sub_issue_is_in_progress_than_feature_also_is_in_progress()
    {
        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $toDoColumn = IssueColumnFactory::todoColumn();
        $inProgressColumn = IssueColumnFactory::inProgressColumn();

        $subIssueType = IssueTypeFactory::subIssueType();

        $feature = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'type' => IssueTypeFactory::featureType(),
            'number' => 1,
            'issueOrder' => 512
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $inProgressColumn,
            'number' => 2,
            'issueOrder' => 1024,
            'type' => $subIssueType,
            'parent' => $feature
        ]);

        IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $toDoColumn,
            'number' => 3,
            'issueOrder' => 2048,
            'type' => $subIssueType,
            'parent' => $feature
        ]);

        $realFeature = $feature->_real();

        $featureStrategy = $this->create($realFeature, $user->_real());;

        $featureStrategy->updateIssueColumn();

        $this->assertTrue($realFeature->getIssueColumn()->isInProgress());
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

    private function create(Issue $issue, User $user): FeatureEditor
    {
        return $this->getService(FeatureEditorFactory::class)->create($issue, $user);
    }
}
