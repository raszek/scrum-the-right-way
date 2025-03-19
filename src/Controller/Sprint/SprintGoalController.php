<?php

namespace App\Controller\Sprint;

use App\Controller\Issue\CommonIssueController;
use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Repository\Sprint\SprintRepository;
use App\Security\Voter\SprintVoter;
use App\Service\Sprint\SprintEditorFactory;
use App\Service\Sprint\SprintGoalEditorFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class SprintGoalController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly SprintGoalRepository $sprintGoalRepository,
        private readonly SprintRepository $sprintRepository,
        private readonly SprintEditorFactory $sprintEditorFactory,
        private readonly SprintGoalEditorFactory $sprintGoalEditorFactory
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/sprints/current/goals/{goalId}/name', 'app_project_sprint_edit_goal_name', methods: ['POST'])]
    public function editSprintGoalName(Project $project, string $goalId, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::EDIT_SPRINT_GOAL_NAME, $project);

        $name = $request->get('name');

        if (!$name) {
            throw new UnprocessableEntityHttpException('No name field was provided');
        }

        $currentSprint = $this->getCurrentSprint($project);

        $sprintGoal = $this->findSprintGoal($goalId, $currentSprint);

        $sprintGoalEditor = $this->sprintGoalEditorFactory->create($sprintGoal);

        $sprintGoalEditor->editName($name);

        return $this->redirectToRoute('app_project_sprint_current_view', [
            'id' => $project->getId(),
        ]);
    }

    #[Route('/sprints/current/goals/{goalId}/remove', 'app_project_sprint_remove_goal', methods: ['POST'])]
    public function removeSprintGoal(Project $project, string $goalId): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::REMOVE_CURRENT_SPRINT_GOAL, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintGoal = $this->findSprintGoal($goalId, $currentSprint);

        if (!$sprintGoal->canBeRemoved()) {
            throw new BadRequestHttpException(
                sprintf('Cannot remove sprint goal. Reason: %s', $sprintGoal->removeErrors()[0])
            );
        }

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        $sprintEditor->removeSprintGoal($sprintGoal);

        return $this->redirectToRoute('app_project_sprint_current_view', [
            'id' => $project->getId(),
        ]);
    }

    private function findSprintGoal(string $goalId, Sprint $sprint): SprintGoal
    {
        $sprintGoal = $this->sprintGoalRepository->findOneBy([
            'sprint' => $sprint,
            'id' => $goalId,
        ]);

        if (!$sprintGoal) {
            throw new NotFoundHttpException('Sprint goal not found');
        }

        return $sprintGoal;
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
