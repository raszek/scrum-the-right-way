<?php

namespace App\Tests\Service\Sprint;

use App\Exception\Sprint\CannotStartSprintException;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Sprint\SprintGoalFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Sprint\SprintGoalIssueFactory;
use App\Form\Sprint\StartSprintForm;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Service\Sprint\SprintEditorFactory;
use App\Tests\KernelTestCase;
use Carbon\CarbonImmutable;

class SprintEditorTest extends KernelTestCase
{

    /** @test */
    public function to_start_sprint_every_sprint_goal_must_have_at_least_one_issue()
    {
        $sprint = SprintFactory::createOne([
            'isCurrent' => true
        ]);

        SprintGoalFactory::createOne([
            'name' => 'Sprint goal name',
            'sprint' => $sprint,
        ]);

        $sprintEditor = $this->factory()->create($sprint);

        $error = null;
        try {
            $sprintEditor->start(new StartSprintForm(
                estimatedEndDate: CarbonImmutable::create(2012, 12, 12)
            ));
        } catch (CannotStartSprintException $e) {
            $error = $e->getMessage();
        }

        $this->assertNotNull($error);
        $this->assertEquals('Cannot start sprint. Every sprint goal must have at least one issue.', $error);
    }

    /** @test */
    public function to_start_sprint_every_sub_issue_and_issue_must_have_estimated_story_points()
    {
        $project = ProjectFactory::createOne();

        $feature = IssueFactory::createOne([
            'project' => $project,
            'type' => IssueTypeFactory::featureType(),
            'number' => 1,
        ]);

        $subIssue = IssueFactory::createOne([
            'project' => $project,
            'type' => IssueTypeFactory::subIssueType(),
            'number' => 2,
            'parent' => $feature,
            'storyPoints' => null,
        ]);

        $sprint = SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project,
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Sprint goal name',
            'sprint' => $sprint,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $feature,
            'sprintGoal' => $sprintGoal,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $subIssue,
            'sprintGoal' => $sprintGoal,
        ]);

        $sprintEditor = $this->factory()->create($sprint);

        $error = null;
        try {
            $sprintEditor->start(new StartSprintForm(
                estimatedEndDate: CarbonImmutable::create(2012, 12, 12)
            ));
        } catch (CannotStartSprintException $e) {
            $error = $e->getMessage();
        }

        $this->assertNotNull($error);
        $this->assertEquals('Cannot start sprint. Every issue and feature sub issue must have story points estimation.', $error);
    }

    /** @test */
    public function when_feature_is_added_to_sprint_also_feature_sub_issues_are_added_to_sprint()
    {
        $project = ProjectFactory::createOne();

        $sprint = SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project,
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Sprint goal name',
            'sprint' => $sprint,
        ]);

        $subIssueType = IssueTypeFactory::subIssueType();

        $backlogColumn = IssueColumnFactory::backlogColumn();
        IssueColumnFactory::todoColumn();

        $feature = IssueFactory::createOne([
            'title' => 'Feature',
            'type' => IssueTypeFactory::featureType(),
            'number' => 1,
            'issueColumn' => $backlogColumn,
            'project' => $project,
        ]);

        $subIssueOne = IssueFactory::createOne([
            'title' => 'Sub issue 1',
            'type' => $subIssueType,
            'parent' => $feature,
            'number' => 2,
            'issueColumn' => $backlogColumn,
            'issueOrder' => 128,
            'project' => $project,
        ]);

        $subIssueTwo = IssueFactory::createOne([
            'title' => 'Sub issue 2',
            'type' => $subIssueType,
            'parent' => $feature,
            'number' => 3,
            'issueColumn' => $backlogColumn,
            'issueOrder' => 256,
            'project' => $project,
        ]);

        $sprintEditor = $this->factory()->create($sprint);

        $sprintEditor->addSprintIssues([
            $feature->_real()
        ]);

        $updatedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoal->getId()
        ]);

        $this->assertNotNull($updatedSprintGoal);
        $this->assertEquals('Feature', $updatedSprintGoal->getSprintGoalIssues()->get(0)->getIssue()->getTitle());
        $this->assertEquals('Sub issue 1', $updatedSprintGoal->getSprintGoalIssues()->get(1)->getIssue()->getTitle());
        $this->assertEquals('Sub issue 2', $updatedSprintGoal->getSprintGoalIssues()->get(2)->getIssue()->getTitle());

        $updatedIssues = $this->issueRepository()->findBy([
            'id' => [
                $feature->getId()->integerId(),
                $subIssueOne->getId()->integerId(),
                $subIssueTwo->getId()->integerId(),
            ],
        ]);

        $this->assertTrue($updatedIssues[0]->getIssueColumn()->isToDo());
        $this->assertTrue($updatedIssues[1]->getIssueColumn()->isToDo());
        $this->assertTrue($updatedIssues[2]->getIssueColumn()->isToDo());
    }

    /** @test */
    public function when_feature_is_removed_from_sprint_removed_are_also_sub_issues()
    {
        $project = ProjectFactory::createOne();

        $sprint = SprintFactory::createOne([
            'isCurrent' => true,
            'project' => $project,
        ]);

        $sprintGoal = SprintGoalFactory::createOne([
            'name' => 'Sprint goal name',
            'sprint' => $sprint,
        ]);

        $subIssueType = IssueTypeFactory::subIssueType();

        IssueColumnFactory::backlogColumn();
        $toDoColumn = IssueColumnFactory::todoColumn();

        $feature = IssueFactory::createOne([
            'title' => 'Feature',
            'type' => IssueTypeFactory::featureType(),
            'number' => 1,
            'issueColumn' => $toDoColumn,
            'project' => $project,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $feature,
            'sprintGoal' => $sprintGoal,
        ]);

        $subIssueOne = IssueFactory::createOne([
            'title' => 'Sub issue 1',
            'type' => $subIssueType,
            'parent' => $feature,
            'number' => 2,
            'issueColumn' => $toDoColumn,
            'issueOrder' => 128,
            'project' => $project,
        ]);

        $subIssueTwo = IssueFactory::createOne([
            'title' => 'Sub issue 2',
            'type' => $subIssueType,
            'parent' => $feature,
            'number' => 3,
            'issueColumn' => $toDoColumn,
            'issueOrder' => 256,
            'project' => $project,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $subIssueOne,
            'sprintGoal' => $sprintGoal,
        ]);

        SprintGoalIssueFactory::createOne([
            'issue' => $subIssueTwo,
            'sprintGoal' => $sprintGoal,
        ]);

        $sprintEditor = $this->factory()->create($sprint);

        $sprintEditor->removeSprintIssue($feature->_real());

        $updatedSprintGoal = $this->sprintGoalRepository()->findOneBy([
            'id' => $sprintGoal->getId()
        ]);

        $this->assertNotNull($updatedSprintGoal);
        $this->assertCount(0, $updatedSprintGoal->getSprintGoalIssues());

        $updatedIssues = $this->issueRepository()->findBy([
            'id' => [
                $feature->getId()->integerId(),
                $subIssueOne->getId()->integerId(),
                $subIssueTwo->getId()->integerId(),
            ],
        ]);

        $this->assertTrue($updatedIssues[0]->getIssueColumn()->isBacklog());
        $this->assertTrue($updatedIssues[1]->getIssueColumn()->isBacklog());
        $this->assertTrue($updatedIssues[2]->getIssueColumn()->isBacklog());
    }

    private function factory(): SprintEditorFactory
    {
        return $this->getService(SprintEditorFactory::class);
    }

    private function sprintGoalRepository(): SprintGoalRepository
    {
        return $this->getService(SprintGoalRepository::class);
    }

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }
}
