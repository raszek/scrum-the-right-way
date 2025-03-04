<?php

namespace App\Controller\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectTag;
use App\Form\Issue\CreateIssueType;
use App\Form\Issue\IssueSearchForm;
use App\Form\Issue\IssueSearchType;
use App\Repository\Issue\AttachmentRepository;
use App\Repository\Issue\IssueDependencyRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Issue\IssueThreadMessageRepository;
use App\Repository\Project\ProjectMemberRepository;
use App\Repository\Project\ProjectTagRepository;
use App\Security\Voter\IssueVoter;
use App\Service\Issue\ProjectIssueEditorFactory;
use App\Service\Issue\Session\IssueSessionSettings;
use App\Service\Issue\StoryPointService;
use App\Table\Issue\IssueTable;
use App\Table\QueryParams;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class IssueController extends CommonIssueController
{

    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly PaginatorInterface $paginator,
        private readonly ProjectIssueEditorFactory $projectIssueEditorFactory,
        private readonly ProjectMemberRepository $projectMemberRepository,
        private readonly StoryPointService $storyPointService,
        private readonly AttachmentRepository $attachmentRepository,
        private readonly IssueThreadMessageRepository $issueThreadMessageRepository,
        private readonly IssueSessionSettings $issueSessionSettings,
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/issues', name: 'app_project_issue_list')]
    public function index(Project $project, Request $request, IssueTable $issueTable): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::LIST_ISSUES, $project);

        $queryParams = QueryParams::fromRequest($request);
        $queryParams->defaultSortField = 'issue.updatedAt';
        $queryParams->defaultSortDirection = 'desc';

        $searchForm = $this->createForm(IssueSearchType::class, new IssueSearchForm($project));

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $queryParams->setFilters($searchForm->getData());
        }

        $table = $issueTable->create($project, $queryParams);

        return $this->render('issue/index.html.twig', [
            'project' => $project,
            'table' => $table,
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/backlog', name: 'app_project_backlog')]
    public function backlog(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::BACKLOG_VIEW, $project);

        $form = $this->createForm(CreateIssueType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(IssueVoter::CREATE_ISSUE, $project);

            $loggedInMember = $project->member($this->getLoggedInUser());
            $projectEditor = $this->projectIssueEditorFactory->create($project, $loggedInMember);

            $projectEditor->createIssue($form->getData());

            return $this->redirectToRoute('app_project_backlog', [
                'id' => $project->getId(),
            ]);
        }

        $page = $request->query->getInt('page', 1);
        $showAll = $request->query->get('showAll') === '1';
        $limit = $showAll ? 999999 : 100;

        $pagination = $this->paginator->paginate(
            $this->issueRepository->backlogQuery($project),
            $page,
            $limit
        );

        return $this->render('issue/backlog.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
            'showAll' => $showAll,
            'showMetric' => !$showAll && $page === 1 && count($pagination->getItems()) > 20,
            'form' => $form
        ]);
    }

    #[Route('/kanban', name: 'app_project_kanban')]
    public function kanban(Project $project): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::KANBAN_VIEW, $project);

        return $this->render('issue/kanban.html.twig', [
            'project' => $project
        ]);
    }

    #[Route(['/issues/{issueCode}', '/backlog/issues/{issueCode}'], name: 'app_project_issue_view')]
    public function view(
        Project $project,
        string $issueCode,
        Request $request,
        ProjectTagRepository $projectTagRepository,
        IssueDependencyRepository $issueDependencyRepository
    ): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::VIEW_ISSUE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $assignees = $this->projectMemberRepository->issueAssignees($issue);

        $attachments = $this->attachmentRepository->issueAttachments($issue);

        $dependencies = $issueDependencyRepository->issueDependencies($issue);

        $subIssues = $this->issueRepository->subIssues($issue);

        $loggedInMember = $project->member($this->getLoggedInUser());

        return $this->render('issue/view.html.twig', [
            'project' => $project,
            'issue' => $issue,
            'loggedInMember' => $loggedInMember,
            'previousSite' => $this->getPreviousSite($request),
            'titleMaxLength' => Issue::TITLE_LENGTH,
            'assignees' => $assignees,
            'storyPoints' => $this->storyPointService->recommendedStoryPoints(),
            'attachments' => $attachments,
            'observers' => $issue->getObservers()->toArray(),
            'isObservedByLoggedIn' => $issue->isObservedBy($loggedInMember),
            'tagInfo' => [
                'maxLength' => ProjectTag::NAME_MAX_LENGTH,
                'maxItems' => Issue::MAX_TAG_COUNT
            ],
            'projectTags' => $projectTagRepository->selectedTags($project, $issue),
            'messages' => $this->issueThreadMessageRepository->getIssueMessages($issue),
            'dependencies' => $dependencies,
            'isActivitiesVisible' => $this->issueSessionSettings->isActivitiesVisible() ? 'true' : 'false',
            'subIssues' => $subIssues
        ]);
    }

    private function getPreviousSite(Request $request): string
    {
        $pathInfo = $request->getPathInfo();

        $parts = explode('/', $pathInfo);

        if (!isset($parts[3])) {
            return 'issues';
        }

        return $parts[3];
    }
}
