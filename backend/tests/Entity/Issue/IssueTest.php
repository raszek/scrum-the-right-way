<?php

namespace App\Tests\Entity\Issue;

use App\Entity\Issue\Issue;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\UserFactory;
use App\Tests\KernelTestCase;
use DateTimeImmutable;

class IssueTest extends KernelTestCase
{

    /** @test */
    public function sub_issue_can_be_initialized()
    {
        $backlogColumn = IssueColumnFactory::backlogColumn();

        $project = ProjectFactory::createOne();

        $user = UserFactory::createOne();

        $feature = new Issue(
            number: 1,
            title: 'Some title',
            columnOrder: 128,
            issueColumn: $backlogColumn,
            type: IssueTypeFactory::featureType(),
            project: $project,
            createdBy: $user,
            createdAt: new DateTimeImmutable(),
        );

        $subIssue = new Issue(
            number: 2,
            title: 'Some title',
            columnOrder: 128,
            issueColumn: $backlogColumn,
            type: IssueTypeFactory::subIssueType(),
            project: $project,
            createdBy: $user,
            createdAt: new DateTimeImmutable(),
            parent: $feature,
            issueOrder: 128
        );

        $this->assertTrue($subIssue->isSubIssue());
    }
}
