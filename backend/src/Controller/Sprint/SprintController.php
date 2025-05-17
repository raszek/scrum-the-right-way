<?php

namespace App\Controller\Sprint;

use App\Controller\Issue\CommonIssueController;
use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Exception\Sprint\CannotStartSprintException;
use App\Form\Sprint\SprintGoalFormType;
use App\Form\Sprint\StartSprintForm;
use App\Form\Sprint\StartSprintType;
use App\Helper\StimulusHelper;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintRepository;
use App\Security\Voter\SprintVoter;
use App\Service\Sprint\BurndownChartService\BurndownChartService;
use App\Service\Sprint\SprintEditorFactory;
use App\Service\Sprint\SprintService;
use App\Table\QueryParams;
use App\Table\Sprint\SprintTable;
use Carbon\CarbonImmutable;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class SprintController extends CommonIssueController
{

    public function __construct(
        private readonly SprintEditorFactory $sprintEditorFactory,
        private readonly IssueRepository $issueRepository,
        private readonly SprintRepository $sprintRepository,
        private readonly SprintService $sprintService,
        private readonly BurndownChartService $burndownChartService,
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/scrum/home', 'app_project_scrum_home')]
    public function home(Project $project): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::SPRINT_HOME, $project);

        $currentSprint = $this->getCurrentSprint($project);

        if ($currentSprint->isStarted()) {
            return $this->overview($currentSprint, $project);
        }

        return $this->render('sprint/home.html.twig', [
            'project' => $project,
        ]);
    }


    #[Route('/sprints/current/plan', 'app_project_sprint_current_plan')]
    public function plan(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::PLAN_CURRENT_SPRINT, $project);

        $currentSprint = $this->getCurrentSprint($project);
        if ($currentSprint->isStarted()) {
            throw new BadRequestHttpException('Sprint is already started. Cannot access sprint planning.');
        }

        $sprintGoals = $this->sprintService->getSprintGoals($currentSprint);

        $startSprintForm = $this->createForm(StartSprintType::class, new StartSprintForm(
            estimatedEndDate: CarbonImmutable::now()->addWeeks(2),
        ));
        $startSprintForm->handleRequest($request);
        if ($startSprintForm->isSubmitted() && $startSprintForm->isValid()) {
            return $this->start($project, $startSprintForm->getData());
        }

        $sprintGoalForm = $this->createForm(SprintGoalFormType::class);
        $sprintGoalForm->handleRequest($request);
        if ($sprintGoalForm->isSubmitted() && $sprintGoalForm->isValid()) {
            $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

            $sprintEditor->addGoal($sprintGoalForm->getData());

            $this->successFlash('Successfully added new sprint goal');

            return $this->redirectToRoute('app_project_sprint_current_plan', [
                'id' => $project->getId(),
            ]);
        }

        return $this->render('sprint/plan.html.twig', [
            'project' => $project,
            'sprint' => $currentSprint,
            'sprintGoals' => $sprintGoals,
            'sprintGoalForm' => $sprintGoalForm,
            'startSprintForm' => $startSprintForm,
        ]);
    }

    #[Route('/sprints/current/finish', 'app_project_sprint_current_finish')]
    public function finish(Project $project): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::FINISH_CURRENT_SPRINT, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        $sprintEditor->finish();

        return $this->redirectToRoute('app_project_scrum_home', [
            'id' => $project->getId(),
        ]);
    }

    #[Route('/sprints', 'app_project_sprint_list')]
    public function list(
        Project $project,
        Request $request,
        SprintTable $sprintTable
    ): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::SPRINT_LIST, $project);

        $queryParams = QueryParams::fromRequest($request);

        $table = $sprintTable->create($project, $queryParams);

        return $this->render('sprint/index.html.twig', [
            'project' => $project,
            'table' => $table
        ]);
    }

    #[Route('/sprints/{sprintId}/view', 'app_project_sprint_view')]
    public function view(Project $project, string $sprintId): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::VIEW_SPRINT, $project);

        $sprint = $this->findSprint($sprintId, $project);

        $chartRecords = $this->burndownChartService->getChartData($sprint);

        $sprintWithIssues = $this->sprintRepository->getSprintIssues($sprint);

        return $this->render('sprint/view.html.twig', [
            'project' => $project,
            'sprint' => $sprintWithIssues,
            'chartRecords' => StimulusHelper::object($chartRecords),
        ]);
    }

    private function overview(Sprint $currentSprint, Project $project): Response
    {
        $chartRecords = $this->burndownChartService->getChartData($currentSprint);

        return $this->render('sprint/overview.html.twig', [
            'project' => $project,
            'sprint' => $currentSprint,
            'chartRecords' => StimulusHelper::object($chartRecords),
            'latestDoneIssues' => $this->sprintService->getLatestDoneIssues($currentSprint),
        ]);
    }

    private function start(Project $project, StartSprintForm $form): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::START_CURRENT_SPRINT, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        try {
            $sprintEditor->start($form);
        } catch (CannotStartSprintException $e) {
            $this->errorFlash($e->getMessage());

            return $this->redirectToRoute('app_project_sprint_current_plan', [
                'id' => $project->getId(),
            ]);
        }

        return $this->redirectToRoute('app_project_kanban', [
            'id' => $project->getId()
        ]);
    }

    private function findSprint(string $sprintId, Project $project): Sprint
    {
        $sprint = $this->sprintRepository->findOneBy([
            'id' => $sprintId,
            'project' => $project,
        ]);

        if (!$sprint) {
            throw new NotFoundHttpException('Sprint not found');
        }

        return $sprint;
    }

    private function getCurrentSprint(Project $project): Sprint
    {
        $sprint = $this->sprintRepository->findOneBy([
            'project' => $project,
            'isCurrent' => true,
        ]);

        if (!$sprint) {
            throw new RuntimeException('Project must have at least one current sprint.');
        }

        return $sprint;
    }
}
