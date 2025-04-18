<?php

namespace App\Controller\Sprint;

use App\Controller\Issue\CommonIssueController;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Entity\Sprint\SprintGoal;
use App\Entity\Sprint\SprintGoalIssue;
use App\Exception\Sprint\CannotAddSprintIssueException;
use App\Exception\Sprint\CannotStartSprintException;
use App\Form\Sprint\SprintGoalIssueMoveForm;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Repository\Sprint\SprintGoalRepository;
use App\Repository\Sprint\SprintRepository;
use App\Security\Voter\SprintVoter;
use App\Service\Sprint\SprintEditorFactory;
use App\Service\Sprint\SprintGoalIssueEditorFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/sprints/current')]
class SprintGoalIssueController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly SprintEditorFactory $sprintEditorFactory,
        private readonly SprintRepository $sprintRepository,
        private readonly SprintGoalRepository $sprintGoalRepository,
        private readonly SprintGoalIssueRepository $sprintGoalIssueRepository,
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/issues/{issueCode}', 'app_project_sprint_add_issue', methods: ['POST'])]
    public function addSprintIssue(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::ADD_CURRENT_SPRINT_ISSUE, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        $issue = $this->findIssue($issueCode, $project);

        try {
            $sprintEditor->addSprintIssue($issue);
        } catch (CannotAddSprintIssueException $e) {
            $this->errorFlash($e->getMessage());
        }

        return $this->redirectToRoute('app_project_backlog', [
            'id'  => $project->getId(),
        ]);
    }

    #[Route('/issues/{issueCode}/remove', 'app_project_sprint_remove_issue', methods: ['POST'])]
    public function removeSprintIssue(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::REMOVE_CURRENT_SPRINT_ISSUE, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        $issue = $this->findIssue($issueCode, $project);

        $sprintEditor->removeSprintIssue($issue);

        return $this->redirectToRoute('app_project_sprint_current_view', [
            'id' => $project->getId(),
        ]);
    }

    #[Route('/issues/{issueCode}/move', 'app_project_sprint_move_issue', methods: ['POST'])]
    public function moveSprintIssue(
        Project $project,
        string $issueCode,
        #[MapRequestPayload] SprintGoalIssueMoveForm $form,
        SprintGoalIssueEditorFactory $factory
    ): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::MOVE_CURRENT_SPRINT_ISSUE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $goalIssue = $this->findSprintGoalIssue($issue, $currentSprint);

        $targetGoal = $this->findSprintGoal($form->goalId, $currentSprint);

        $editor = $factory->create($goalIssue);

        $editor->move($targetGoal, $form->position);

        return new Response(status: 204);
    }

    private function findSprintGoal(string $goalId, Sprint $sprint): SprintGoal
    {
        $goal = $this->sprintGoalRepository->findOneBy([
            'id' => $goalId,
            'sprint' => $sprint
        ]);

        if (!$goal) {
            throw new NotFoundHttpException('Sprint goal not found.');
        }

        return $goal;
    }

    private function findSprintGoalIssue(Issue $issue, Sprint $sprint): SprintGoalIssue
    {
        $goalIssue = $this->sprintGoalIssueRepository->findSprintIssue($issue, $sprint);

        if (!$goalIssue) {
            throw new NotFoundHttpException('Goal issue not found');
        }

        return $goalIssue;
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
