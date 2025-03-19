<?php

namespace App\Tests\Service\Issue;


use App\Entity\Issue\Issue;
use App\Entity\User\User;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\UserFactory;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueRepository;
use App\Service\Issue\FeatureEditor;
use App\Service\Issue\FeatureEditorFactory;
use App\Tests\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

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

    private function issueRepository(): IssueRepository
    {
        return $this->getService(IssueRepository::class);
    }

    private function create(Issue $issue, User $user): FeatureEditor
    {
        return $this->getService(FeatureEditorFactory::class)->create($issue, $user);
    }
}
