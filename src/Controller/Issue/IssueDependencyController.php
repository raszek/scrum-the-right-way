<?php

namespace App\Controller\Issue;

use App\Entity\Project\Project;
use App\Exception\Issue\CannotAddIssueDependencyException;
use App\Exception\Issue\CannotRemoveIssueDependencyException;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\IssueDependencyVoter;
use App\Service\Issue\DependencyIssueEditorFactory;
use App\Service\Issue\DependencyIssueService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/issues/{issueCode}')]
class IssueDependencyController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly DependencyIssueEditorFactory $dependencyIssueEditorFactory,
        private readonly DependencyIssueService $dependencyIssueService
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/dependencies/{dependencyCode}/add', name: 'app_project_issue_add_issue_dependency', methods: ['POST'])]
    public function addDependency(Project $project, string $issueCode, string $dependencyCode): Response
    {
        $this->denyAccessUnlessGranted(IssueDependencyVoter::ISSUE_ADD_DEPENDENCY, $project);

        $issue = $this->findIssue($issueCode, $project);

        $dependency = $this->findIssue($dependencyCode, $project);

        $dependencyIssueEditor = $this->dependencyIssueEditorFactory->create($issue, $this->getLoggedInUser());

        try {
            $dependencyIssueEditor->addDependency($dependency);
        } catch (CannotAddIssueDependencyException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 201);
    }

    #[Route('/dependencies/{dependencyCode}/remove', name: 'app_project_issue_remove_issue_dependency', methods: ['POST'])]
    public function removeDependency(Project $project, string $issueCode, string $dependencyCode): Response
    {
        $this->denyAccessUnlessGranted(IssueDependencyVoter::ISSUE_REMOVE_DEPENDENCY, $project);

        $issue = $this->findIssue($issueCode, $project);

        $dependency = $this->findIssue($dependencyCode, $project);

        $dependencyIssueEditor = $this->dependencyIssueEditorFactory->create($issue, $this->getLoggedInUser());

        try {
            $dependencyIssueEditor->removeDependency($dependency);
        } catch (CannotRemoveIssueDependencyException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 204);
    }

    #[Route('/dependencies', name: 'app_project_issue_list_dependencies', methods: ['GET'])]
    public function listDependencies(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(IssueDependencyVoter::ISSUE_LIST_DEPENDENCIES, $project);

        $issue = $this->findIssue($issueCode, $project);

        $search = $request->get('search');

        if (!$search) {
            throw new BadRequestHttpException('No search parameter found');
        }

        $result = $this->dependencyIssueService->searchDependencies($issue, $search);

        return new JsonResponse($result);
    }

}
