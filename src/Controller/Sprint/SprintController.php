<?php

namespace App\Controller\Sprint;

use App\Controller\Issue\CommonIssueController;
use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Exception\Sprint\CannotStartSprintException;
use App\Form\Sprint\SprintGoalFormType;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintRepository;
use App\Security\Voter\SprintVoter;
use App\Service\Sprint\SprintAccess;
use App\Service\Sprint\SprintEditorFactory;
use App\Service\Sprint\SprintService;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class SprintController extends CommonIssueController
{

    public function __construct(
        private readonly SprintEditorFactory $sprintEditorFactory,
        private readonly IssueRepository $issueRepository,
        private readonly SprintRepository $sprintRepository,
        private readonly SprintService $sprintService,
    ) {
        parent::__construct($this->issueRepository);
    }


    #[Route('/sprints/current', 'app_project_sprint_current_view')]
    public function viewCurrent(Project $project, Request $request, SprintAccess $sprintAccess): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::VIEW_CURRENT_SPRINT, $project);

        $error = $sprintAccess->sprintViewAccessError($project);
        if ($error) {
            throw new BadRequestHttpException($error);
        }

        $currentSprint = $this->getCurrentSprint($project);

        $sprintGoals = $this->sprintService->getSprintGoals($currentSprint);

        $sprintGoalForm = $this->createForm(SprintGoalFormType::class);

        $sprintGoalForm->handleRequest($request);
        if ($sprintGoalForm->isSubmitted() && $sprintGoalForm->isValid()) {

            $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

            $sprintEditor->addGoal($sprintGoalForm->getData());

            $this->successFlash('Successfully added new sprint goal');

            return $this->redirectToRoute('app_project_sprint_current_view', [
                'id' => $project->getId(),
            ]);
        }

        return $this->render('sprint/view.html.twig', [
            'project' => $project,
            'sprint' => $currentSprint,
            'sprintGoals' => $sprintGoals,
            'sprintGoalForm' => $sprintGoalForm,
        ]);
    }

    #[Route('/sprints/current/start', 'app_project_sprint_current_start', methods: ['POST'])]
    public function start(Project $project): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::START_CURRENT_SPRINT, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        try {
            $sprintEditor->start();
        } catch (CannotStartSprintException $e) {

            $this->errorFlash($e->getMessage());

            return $this->redirectToRoute('app_project_sprint_current_view', [
                'id' => $project->getId(),
            ]);
        }

        return $this->redirectToRoute('app_project_kanban', [
            'id' => $project->getId()
        ]);
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
