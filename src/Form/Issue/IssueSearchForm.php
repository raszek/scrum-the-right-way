<?php

namespace App\Form\Issue;

use App\Entity\Issue\IssueColumn;
use App\Entity\Issue\IssueType;
use App\Entity\Project\Project;
use App\Entity\User\User;
use DateTimeImmutable;

class IssueSearchForm
{

    public function __construct(
        public Project $project,
        public ?string            $title = null,
        public ?int               $number = null,
        public ?IssueColumn       $column = null,
        public ?IssueType         $type = null,
        public ?User              $createdBy = null,
        public ?DateTimeImmutable $createdAfter = null,
        public ?DateTimeImmutable $createdBefore = null,
        public ?DateTimeImmutable $updatedAfter = null,
        public ?DateTimeImmutable $updatedBefore = null,
    ) {
    }

}
