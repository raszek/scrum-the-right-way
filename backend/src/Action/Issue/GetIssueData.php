<?php

namespace App\Action\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Project\ProjectTag;
use App\Entity\User\User;
use App\Repository\Issue\AttachmentRepository;
use App\Repository\Issue\IssueDependencyRepository;
use App\Repository\Issue\IssueRepository;
use App\Repository\Issue\IssueThreadMessageRepository;
use App\Repository\Project\ProjectMemberRepository;
use App\Repository\Project\ProjectTagRepository;
use App\Repository\Sprint\SprintRepository;
use App\Service\Issue\IssueEditor\IssueEditorFactory;
use App\Service\Issue\StoryPointService;
use App\Service\Session\Issue\IssueSessionSettings;

readonly class GetIssueData
{
    public function __construct(

        private ProjectMemberRepository $projectMemberRepository,
        private StoryPointService $storyPointService,
        private AttachmentRepository $attachmentRepository,
        private IssueThreadMessageRepository $issueThreadMessageRepository,
        private IssueSessionSettings $issueSessionSettings,
        private ProjectTagRepository $projectTagRepository,
        private IssueDependencyRepository $issueDependencyRepository,
        private IssueEditorFactory $issueEditorFactory,
        private IssueRepository $issueRepository,
        private SprintRepository $sprintRepository,
    ) {
    }

    public function execute(Issue $issue, User $user): array
    {
        $assignees = $this->projectMemberRepository->issueAssignees($issue);

        $attachments = $this->attachmentRepository->issueAttachments($issue);

        $dependencies = $this->issueDependencyRepository->issueDependencies($issue);

        $subIssues = $this->issueRepository->featureSubIssues($issue);

        $loggedInMember = $issue->getProject()->member($user);

        $issueEditor = $this->issueEditorFactory->create($issue, $user);

        return [
            'project' => $issue->getProject(),
            'issue' => $issue,
            'loggedInMember' => $loggedInMember,
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
            'projectTags' => $this->projectTagRepository->selectedTags($issue->getProject(), $issue),
            'messages' => $this->issueThreadMessageRepository->getIssueMessages($issue),
            'dependencies' => $dependencies,
            'isActivitiesVisible' => $this->issueSessionSettings->isActivitiesVisible() ? 'true' : 'false',
            'subIssues' => $subIssues,
            'isIssueEditable' => $issueEditor->isIssueEditable(),
            'isPreviousStoryPointsVisible' => $this->storyPointService->isPreviousStoryPointsVisible($issue),
        ];
    }

}
