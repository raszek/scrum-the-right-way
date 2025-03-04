<?php

namespace App\Controller\Issue;

use App\Entity\Project\Project;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\SubIssueVoter;
use App\Service\Common\FormValidator;
use App\Service\Issue\SubIssueEditorFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/issues/{issueCode}')]
class SubIssueController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly FormValidator $formValidator,
        private readonly SubIssueEditorFactory $subIssueEditorFactory,
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/sub-issues', name: 'app_project_issue_add_sub_issue', methods: ['POST'])]
    public function addSubIssue(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SubIssueVoter::ADD_ISSUE_SUB, $project);

        $issue = $this->findIssue($issueCode, $project);

        $form = SubIssueForm::fromRequest($request);

        $this->formValidator->validate($form);

        $editor = $this->subIssueEditorFactory->create($issue, $this->getLoggedInUser());

        $createdSubIssue = $editor->add($form);

        return $this->render('issue/sub_issue.html.twig', [
            'subIssue' => $createdSubIssue,
            'project' => $project,
        ], new Response(status: 201));
    }

}
