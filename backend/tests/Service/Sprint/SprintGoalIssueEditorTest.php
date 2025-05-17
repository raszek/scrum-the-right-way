<?php

namespace App\Tests\Service\Sprint;


use App\Entity\Sprint\SprintGoalIssue;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Sprint\SprintGoalFactory;
use App\Factory\Sprint\SprintGoalIssueFactory;
use App\Service\Sprint\SprintGoalIssueEditor;
use App\Service\Sprint\SprintGoalIssueEditorFactory;
use App\Tests\KernelTestCase;

class SprintGoalIssueEditorTest extends KernelTestCase
{

    /** @test */
    public function moving_feature_from_sprint_goal_to_another_sprint_goal_is_also_moving_his_sub_issues()
    {
        $project = ProjectFactory::createOne();

        $backlogColumn = IssueColumnFactory::backlogColumn();
        IssueColumnFactory::todoColumn();

        $featureType = IssueTypeFactory::featureType();

        $feature = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $featureType,
            'number' => 1,
        ]);

        $subIssue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => IssueTypeFactory::subIssueType(),
            'number' => 2,
            'parent' => $feature,
            'issueOrder' => 128,
        ]);

        $sprint = SprintFactory::createOne([
            'project' => $project,
            'isCurrent' => true
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'sprint' => $sprint,
        ]);

        $anotherSprintGoal = SprintGoalFactory::createOne([
            'sprint' => $sprint,
        ]);

        $sprintGoalFeature = SprintGoalIssueFactory::createOne([
            'sprintGoal' => $sprintGoal,
            'issue' => $feature,
        ]);

        SprintGoalIssueFactory::createOne([
            'sprintGoal' => $sprintGoal,
            'issue' => $subIssue,
        ]);

        $editor = $this->create($sprintGoalFeature->_real());

        $editor->move($anotherSprintGoal->_real(), 1);

        $sprintGoal->_refresh();
        $this->assertCount(0, $sprintGoal->getSprintGoalIssues());

        $anotherSprintGoal->_refresh();
        $this->assertCount(2, $anotherSprintGoal->getSprintGoalIssues());
    }

    private function create(SprintGoalIssue $sprintGoalIssue): SprintGoalIssueEditor
    {
        $factory = $this->getService(SprintGoalIssueEditorFactory::class);

        return $factory->create($sprintGoalIssue);
    }
}
