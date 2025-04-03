<?php

namespace App\Tests\Service\Sprint;

use App\Exception\Sprint\CannotStartSprintException;
use App\Factory\Sprint\SprintFactory;
use App\Factory\Sprint\SprintGoalFactory;
use App\Service\Sprint\SprintEditorFactory;
use App\Tests\KernelTestCase;

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
            $sprintEditor->start();
        } catch (CannotStartSprintException $e) {
            $error = $e->getMessage();
        }

        $this->assertNotNull($error);
        $this->assertEquals('Cannot start sprint. Every sprint goal must have at least one issue.', $error);
    }

    private function factory(): SprintEditorFactory
    {
        return $this->getService(SprintEditorFactory::class);
    }
}
