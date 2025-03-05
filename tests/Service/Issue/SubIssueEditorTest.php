<?php

namespace App\Tests\Service\Issue;


use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueRepository;
use App\Service\Issue\SubIssueEditor;
use App\Service\Issue\SubIssueEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class SubIssueEditorTest extends KernelTestCase
{

    use Factories;

    /** @test */
    public function when_there_is_no_space_left_for_first_sub_issue_every_feature_issue_should_be_reordered()
    {
        $developer = UserFactory::createOne();

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


        $editor = $this->create($feature->_real(), $developer->_real());

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

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

    private function create(Issue $issue, User $user): SubIssueEditor
    {
        return $this->getService(SubIssueEditorFactory::class)->create($issue, $user);
    }
}
