<?php

namespace App\Controller\Issue;

use App\Entity\Project\Project;
use App\Entity\Thread\ThreadMessage;
use App\Exception\Issue\CannotAddIssueThreadMessageException;
use App\Exception\Issue\CannotRemoveIssueThreadMessageException;
use App\Repository\Issue\IssueRepository;
use App\Repository\Thread\ThreadMessageRepository;
use App\Security\Voter\IssueThreadMessageVoter;
use App\Service\Issue\IssueThreadMessageService;
use App\Service\Issue\ThreadMessageIssueEditorFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/issues/{issueCode}')]
class IssueThreadMessageController extends CommonIssueController
{
    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly ThreadMessageRepository $threadMessageRepository,
        private readonly ThreadMessageIssueEditorFactory $threadMessageIssueEditorFactory,
        private readonly IssueThreadMessageService $issueThreadMessageService
    ) {
        parent::__construct($this->issueRepository);
    }

    #[Route('/messages/{messageId}/add', name: 'app_project_issue_add_thread_message', methods: ['POST'])]
    public function addThreadMessage(Project $project, string $issueCode, string $messageId): Response
    {
        $this->denyAccessUnlessGranted(IssueThreadMessageVoter::ISSUE_ADD_THREAD_MESSAGE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $threadMessage = $this->findThreadMessage($messageId, $project);

        $issueEditor = $this->threadMessageIssueEditorFactory->create($issue, $this->getLoggedInUser());

        try {
            $issueEditor->addMessage($threadMessage);
        } catch (CannotAddIssueThreadMessageException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 201);
    }

    #[Route('/messages/{messageId}/remove', name: 'app_project_issue_remove_thread_message', methods: ['POST'])]
    public function removeThreadMessage(Project $project, string $issueCode, string $messageId): Response
    {
        $this->denyAccessUnlessGranted(IssueThreadMessageVoter::ISSUE_REMOVE_THREAD_MESSAGE, $project);

        $issue = $this->findIssue($issueCode, $project);

        $threadMessage = $this->findThreadMessage($messageId, $project);

        $issueEditor = $this->threadMessageIssueEditorFactory->create($issue, $this->getLoggedInUser());

        try {
            $issueEditor->removeMessage($threadMessage);
        } catch (CannotRemoveIssueThreadMessageException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 204);
    }

    #[Route('/messages', name: 'app_project_issue_list_thread_messages', methods: ['GET'])]
    public function listThreadMessages(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(IssueThreadMessageVoter::ISSUE_LIST_THREAD_MESSAGES, $project);

        $search = $request->query->get('search');

        $limit = $request->query->getInt('limit', 10);

        if (!$search) {
            throw new BadRequestHttpException('Search parameter not found');
        }

        $issue = $this->findIssue($issueCode, $project);

        $result = $this->issueThreadMessageService->searchIssueMessages($search, $issue, $limit);

        return new JsonResponse($result);
    }

    private function findThreadMessage(string $messageId, Project $project): ThreadMessage
    {
        $threadMessage = $this->threadMessageRepository->findOneBy([
            'id' => $messageId,
        ]);

        if (!$threadMessage || $threadMessage->getThread()->getProject()->getId() !== $project->getId()) {
            throw new NotFoundHttpException('Thread message not found');
        }

        return $threadMessage;
    }
}
