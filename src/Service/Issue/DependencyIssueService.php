<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class DependencyIssueService
{

    public function __construct(
        private IssueRepository $issueRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @param Issue $issue
     * @param string $search
     * @return Issue[]
     */
    public function searchDependencies(Issue $issue, string $search): array
    {
        $foundIssues = $this->issueRepository->searchIssueDependencies($issue, $search);

        $project = $issue->getProject();

        return ArrayHelper::map(
            $foundIssues,
            fn(Issue $foundIssue) => [
                'text' => $foundIssue->prefixCodeTitle(),
                'value' => $foundIssue->getCode(),
                'url' => $this->urlGenerator->generate('app_project_issue_view', [
                    'id' => $project->getId(),
                    'issueCode' => $foundIssue->getCode(),
                ]),
                'addUrl' => $this->urlGenerator->generate('app_project_issue_add_issue_dependency', [
                    'id' => $project->getId(),
                    'issueCode' => $issue->getCode(),
                    'dependencyCode' => $foundIssue->getCode(),
                ]),
                'removeUrl' => $this->urlGenerator->generate('app_project_issue_remove_issue_dependency', [
                    'id' => $project->getId(),
                    'issueCode' => $issue->getCode(),
                    'dependencyCode' => $foundIssue->getCode(),
                ]),
            ]
        );
    }
}
