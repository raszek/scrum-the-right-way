<?php

namespace App\Controller\Issue;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Exception\Issue\CannotSetIssueTitleException;
use App\Exception\Issue\CannotSetStoryPointsException;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Form\Tag\TagsForm;
use App\Helper\IntegerHelper;
use App\Repository\Issue\IssueRepository;
use App\Repository\Project\ProjectMemberRepository;
use App\Security\Voter\IssueVoter;
use App\Security\Voter\SingleIssueVoter;
use App\Service\Assignee\IssueAssigneeEditorFactory;
use App\Service\Event\EventService;
use App\Service\Issue\IssueEditorFactory;
use App\Service\Tag\IssueTagEditorFactory;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/projects/{id}/issues/{issueCode}')]
class SingleIssueController extends CommonIssueController
{
    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly IssueAssigneeEditorFactory $issueAssigneeEditorFactory,
        private readonly ProjectMemberRepository $projectMemberRepository,
        private readonly IssueEditorFactory $issueEditorFactory,
        private readonly EventService $eventService,
        private readonly ValidatorInterface $validator,
        private readonly IssueTagEditorFactory $issueTagEditorFactory
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/update-title', name: 'app_project_issue_update_title', methods: ['POST'])]
    public function updateTitle(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::UPDATE_ISSUE_TITLE, $project);

        $title = $request->get('title');

        if (!$title) {
            throw new UnprocessableEntityHttpException('Title must not be empty');
        }

        $issue = $this->findIssue($issueCode, $project);

        $issueEditor = $this->issueEditorFactory->create($issue, $this->getLoggedInUser());

        try {
            $issueEditor->updateTitle($title);
        } catch (CannotSetIssueTitleException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/update-description', name: 'app_project_issue_update_description', methods: ['POST'])]
    public function updateDescription(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::UPDATE_ISSUE_DESCRIPTION, $project);

        $description = $request->get('description');

        $issue = $this->findIssue($issueCode, $project);

        $issueEditor = $this->issueEditorFactory->create($issue, $this->getLoggedInUser());

        $issueEditor->updateDescription($description);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/assignees', 'app_project_issue_assignee_set', methods: ['POST'])]
    public function setAssignee(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::ASSIGNEE_SET, $project);

        $issue = $this->findIssue($issueCode, $project);
        $issueEditor = $this->issueAssigneeEditorFactory->create($issue, $this->getLoggedInUser());

        $issueEditor->setAssignee($this->findProjectMember($request->get('projectMemberId'), $project));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/sort', name: 'app_project_issue_sort', methods: ['POST'])]
    public function sort(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::SORT_ISSUE, $project);

        $position = $request->get('position');

        if (!$position) {
            throw new BadRequestException('No position set');
        }

        $issue = $this->findIssue($issueCode, $project);
        $issueEditor = $this->issueEditorFactory->create($issue, $this->getLoggedInUser());

        try {
            $issueEditor->setPosition($position);
        } catch (OutOfBoundPositionException $e) {
            throw new BadRequestException($e->getMessage());
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route(['/events'], name: 'app_project_issue_view_events')]
    public function events(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::VIEW_ISSUE_EVENTS, $project);

        $issue = $this->findIssue($issueCode, $project);

        $eventRecords = $this->eventService->getIssueEventRecords($issue);

        return $this->render('issue/events.html.twig', [
            'eventRecords' => $eventRecords
        ]);
    }

    #[Route('/set-story-points', 'app_project_issue_story_points_set', methods: ['POST'])]
    public function setStoryPoints(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::STORY_POINTS_SET, $project);

        $issue = $this->findIssue($issueCode, $project);
        $issueEditor = $this->issueEditorFactory->create($issue, $this->getLoggedInUser());

        $storyPoints = $request->get('points');

        if ($storyPoints && !IntegerHelper::isInteger($storyPoints)) {
            throw new UnprocessableEntityHttpException('[points] must be integer');
        }

        try {
            $issueEditor->setStoryPoints($storyPoints);
        } catch (CannotSetStoryPointsException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/tags', 'app_project_issue_tags_set', methods: ['POST'])]
    public function setTags(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::UPDATE_ISSUE_TAGS, $project);

        $form = new TagsForm(
            $request->get('tags')
        );

        $errors = $this->validator->validate($form);

        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException($errors);
        }

        $issue = $this->findIssue($issueCode, $project);
        $issueTagEditor = $this->issueTagEditorFactory->create($issue, $this->getLoggedInUser());
        $issueTagEditor->setTags($form->tags());

        return new Response(status: 204);
    }

    #[Route('/archive', 'app_project_issue_archive', methods: ['POST'])]
    public function archive(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(SingleIssueVoter::UPDATE_ISSUE_ARCHIVE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $issueEditor = $this->issueEditorFactory->create($issue, $this->getLoggedInUser());

        $issueEditor->archive();

        return new Response(status: 204);
    }

    private function findProjectMember(?string $projectMemberId, Project $project): ?ProjectMember
    {
        if (!$projectMemberId) {
            return null;
        }

        return $this->projectMemberRepository->findById($projectMemberId, $project)
            ?? throw new NotFoundHttpException('Project member not found');
    }

}
