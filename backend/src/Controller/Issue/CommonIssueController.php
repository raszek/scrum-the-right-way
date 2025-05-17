<?php

namespace App\Controller\Issue;

use App\Controller\Controller;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Repository\Issue\IssueRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommonIssueController extends Controller
{
    public function __construct(
        private readonly IssueRepository $issueRepository
    ) {
    }

    protected function findIssue(string $issueCode, Project $project): Issue
    {
        $issue = $this->issueRepository->findByCode($issueCode, $project);

        if (!$issue) {
            throw new NotFoundHttpException('Issue not found');
        }

        return $issue;
    }
}
