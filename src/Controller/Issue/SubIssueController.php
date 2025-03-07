<?php

namespace App\Controller\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Form\Issue\SubIssueForm;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\SubIssueVoter;
use App\Service\Common\FormValidator;
use App\Service\Issue\FeatureEditorFactory;
use App\Service\Issue\SubIssueEditorFactory;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class SubIssueController extends CommonIssueController
{

    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly FormValidator $formValidator,
        private readonly FeatureEditorFactory $featureEditorFactory,
        private readonly SubIssueEditorFactory $subIssueEditorFactory
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/issues/{issueCode}/sub-issues', name: 'app_project_issue_add_sub_issue', methods: ['POST'])]
    public function add(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SubIssueVoter::ADD_ISSUE_SUB_ISSUE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $form = SubIssueForm::fromRequest($request);

        $this->formValidator->validate($form);

        $editor = $this->featureEditorFactory->create($issue, $this->getLoggedInUser());

        $createdSubIssue = $editor->add($form);

        sleep(10);

        return $this->render('issue/sub_issue.html.twig', [
            'subIssue' => $createdSubIssue,
            'project' => $project,
        ], new Response(status: 201));
    }

    #[Route('/sub-issues/{subIssueCode}/sort', name: 'app_project_issue_sub_issue_sort', methods: ['POST'])]
    public function sort(Project $project, string $subIssueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SubIssueVoter::SORT_SUB_ISSUE, $project);

        $position = $request->get('position');

        if (!$position) {
            throw new UnprocessableEntityHttpException('Position parameter is required.');
        }

        $subIssue = $this->findSubIssue($subIssueCode, $project);

        $editor = $this->subIssueEditorFactory->create($subIssue);

        try {
            $editor->setPosition($position);
        } catch (OutOfBoundPositionException $e) {
            throw new BadRequestException($e->getMessage());
        }

        return new Response(status: 204);
    }

    private function findSubIssue(string $subIssueCode, Project $project): Issue
    {
        $subIssue = $this->issueRepository->findByCode($subIssueCode, $project);

        if (!$subIssue) {
            throw new NotFoundHttpException('Sub issue not found.');
        }

        return $subIssue;
    }
}
