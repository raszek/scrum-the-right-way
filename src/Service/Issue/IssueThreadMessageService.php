<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Thread\ThreadMessage;
use App\Helper\ArrayHelper;
use App\Repository\Thread\ThreadMessageRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class IssueThreadMessageService
{

    public function __construct(
        private ThreadMessageRepository $threadMessageRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function searchIssueMessages(string $search, Issue $issue, int $limit = 10): array
    {
        $threadMessages = $this->threadMessageRepository->searchIssueMessages($search, $issue, $limit);

        $project = $issue->getProject();

        return ArrayHelper::map($threadMessages, fn(ThreadMessage $message) => [
            'value' => $message->getId()->get(),
            'text' => $message->getIssueTitle(),
            'url' => $this->urlGenerator->generate('app_project_thread_messages', [
                'id' => $project->getId(),
                'threadId' => $message->getThread()->getId(),
                'slug' => $message->getThread()->getSlug(),
            ]).'#'.$message->getNumber(),
            'addUrl' => $this->urlGenerator->generate('app_project_issue_add_thread_message', [
                'id' => $project->getId(),
                'issueCode' => $issue->getCode(),
                'messageId' => $message->getId(),
            ]),
            'removeUrl' => $this->urlGenerator->generate('app_project_issue_remove_thread_message', [
                'id' => $project->getId(),
                'issueCode' => $issue->getCode(),
                'messageId' => $message->getId(),
            ]),
        ]);
    }

}
