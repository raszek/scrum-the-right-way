<?php

namespace App\Controller\Sprint;

use App\Controller\Issue\CommonIssueController;
use App\Entity\Project\Project;
use App\Entity\Sprint\Sprint;
use App\Repository\Issue\IssueRepository;
use App\Repository\Sprint\SprintRepository;
use App\Security\Voter\SprintVoter;
use App\Service\Sprint\SprintEditorFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class SprintController extends CommonIssueController
{

    public function __construct(
        private readonly SprintEditorFactory $sprintEditorFactory,
        private readonly IssueRepository $issueRepository,
        private readonly SprintRepository $sprintRepository,
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/sprints/current/issues/{issueCode}', 'app_project_sprint_add_issue', methods: ['POST'])]
    public function addSprintIssue(Project $project, string $issueCode): Response
    {
        $this->denyAccessUnlessGranted(SprintVoter::ADD_CURRENT_SPRINT_ISSUE, $project);

        $currentSprint = $this->getCurrentSprint($project);

        $sprintEditor = $this->sprintEditorFactory->create($currentSprint);

        $issue = $this->findIssue($issueCode, $project);

        $sprintEditor->addSprintIssue($issue);

        return $this->redirectToRoute('app_project_backlog', [
            'id'  => $project->getId(),
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
