<?php

namespace App\Controller;

use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Repository\Issue\DescriptionHistoryRepository;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\DescriptionHistoryVoter;
use App\Service\DescriptionHistory\DescriptionHistoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/issues/{issueCode}')]
class DescriptionHistoryController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly DescriptionHistoryService $descriptionHistoryService,
        private readonly DescriptionHistoryRepository $descriptionHistoryRepository
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/description-history', name: 'app_issue_description_history_list')]
    public function index(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(DescriptionHistoryVoter::ISSUE_DESCRIPTION_HISTORY_LIST, $project);

        $issue = $this->findIssue($issueCode, $project);

        $pagination = $this->descriptionHistoryService->getItems(
            $issue,
            $request->query->getInt('page', 1)
        );

        return $this->render('description_history/index.html.twig', [
            'project' => $project,
            'issue' => $issue,
            'pagination' => $pagination
        ]);
    }


    #[Route('/description-history/{historyId}', name: 'app_issue_description_history_view')]
    public function view(Project $project, string $issueCode, string $historyId): Response
    {
        $this->denyAccessUnlessGranted(DescriptionHistoryVoter::ISSUE_DESCRIPTION_HISTORY_VIEW, $project);

        $issue = $this->findIssue($issueCode, $project);

        $descriptionHistory = $this->findHistory($historyId, $issue);

        $textDifference = $this->descriptionHistoryService->getChanges($descriptionHistory);

        return $this->render('description_history/view.html.twig', [
            'textDifference' => $textDifference,
        ]);
    }

    #[Route('/description-history/{historyId}/show', name: 'app_issue_description_history_show')]
    public function show(Project $project, string $issueCode, string $historyId): Response
    {
        $this->denyAccessUnlessGranted(DescriptionHistoryVoter::ISSUE_DESCRIPTION_HISTORY_VIEW, $project);

        $issue = $this->findIssue($issueCode, $project);

        $descriptionHistory = $this->findHistory($historyId, $issue);

        $textDifference = $this->descriptionHistoryService->getChanges($descriptionHistory);

        return $this->render('description_history/show.html.twig', [
            'project' => $project,
            'issue' => $issue,
            'history' => $descriptionHistory,
            'textDifference' => $textDifference,
        ]);
    }

    private function findHistory(string $historyId, Issue $issue): DescriptionHistory
    {
        $history = $this->descriptionHistoryRepository->findOneBy([
            'issue' => $issue,
            'id' => $historyId
        ]);

        if (!$history) {
            throw new NotFoundHttpException('Description history not found');
        }

        return $history;
    }
}
