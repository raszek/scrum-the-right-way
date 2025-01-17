<?php

namespace App\Controller;

use App\Entity\Project\Project;
use App\Exception\Observer\CannotAddIssueObserverException;
use App\Exception\Observer\CannotRemoveIssueObserverException;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\IssueObserverVoter;
use App\Service\Observer\IssueObserverEditorFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/issues/{issueCode}')]
class IssueObserverController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly IssueObserverEditorFactory $issueObserverEditorFactory
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/observe', name: 'app_project_issue_observe', methods: ['POST'])]
    public function observe(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(IssueObserverVoter::OBSERVE_ISSUE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $member = $project->member($this->getLoggedInUser());

        $editor = $this->issueObserverEditorFactory->create($issue);

        try {
            $editor->addObserver($member);
        } catch (CannotAddIssueObserverException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 204);
    }

    #[Route('/unobserve', name: 'app_project_issue_unobserve', methods: ['POST'])]
    public function unobserve(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(IssueObserverVoter::UNOBSERVE_ISSUE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $member = $project->member($this->getLoggedInUser());

        $editor = $this->issueObserverEditorFactory->create($issue);

        try {
            $editor->removeObserver($member);
        } catch (CannotRemoveIssueObserverException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 204);
    }
}
