<?php

namespace App\Tests\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Exception\Issue\CannotAddIssueDependencyException;
use App\Factory\Issue\IssueDependencyFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Service\Issue\DependencyIssueEditor;
use App\Service\Issue\DependencyIssueEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class DependencyIssueEditorTest extends KernelTestCase
{



    /** @test */
    public function issue_cannot_add_yourself_as_dependency()
    {
        $user = UserFactory::createOne();

        $issue = IssueFactory::createOne();

        $editor = $this->create($issue->_real(), $user->_real());

        $exception = null;
        try {
            $editor->addDependency($issue->_real());
        } catch (CannotAddIssueDependencyException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
        $this->assertEquals('Issue cannot add itself as dependency', $exception->getMessage());
    }

    /** @test */
    public function issue_cannot_add_twice_same_dependency()
    {
        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'number' => 1
        ]);

        $anotherIssue = IssueFactory::createOne([
            'project' => $project,
            'number' => 2,
        ]);

        IssueDependencyFactory::createOne([
            'issue' => $issue,
            'dependency' => $anotherIssue,
        ]);

        $editor = $this->create($issue->_real(), $user->_real());

        $exception = null;
        try {
            $editor->addDependency($anotherIssue->_real());
        } catch (CannotAddIssueDependencyException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
        $this->assertEquals('Cannot add SCP-2 as dependency second time', $exception->getMessage());
    }

    private function create(Issue $issue, User $user): DependencyIssueEditor
    {
        $factory = $this->getService(DependencyIssueEditorFactory::class);

        return $factory->create($issue, $user);
    }
}
