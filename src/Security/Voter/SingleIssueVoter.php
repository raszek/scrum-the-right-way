<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SingleIssueVoter extends Voter
{
    public const ASSIGNEE_SET = 'ASSIGNEE_SET';

    public const STORY_POINTS_SET = 'STORY_POINTS_SET';

    public const UPDATE_ISSUE_TAGS = 'UPDATE_ISSUE_TAGS';

    public const UPDATE_ISSUE_TITLE = 'UPDATE_ISSUE_TITLE';

    public const UPDATE_ISSUE_DESCRIPTION = 'UPDATE_ISSUE_DESCRIPTION';

    public const SORT_ISSUE = 'SORT_ISSUE';

    public const VIEW_ISSUE_EVENTS = 'VIEW_ISSUE_EVENTS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::ASSIGNEE_SET,
            self::STORY_POINTS_SET,
            self::SORT_ISSUE,
            self::UPDATE_ISSUE_TITLE,
            self::UPDATE_ISSUE_DESCRIPTION,
            self::VIEW_ISSUE_EVENTS,
            self::UPDATE_ISSUE_TAGS
        ];

        return in_array($attribute, $attributes) && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /**
         * @var Project $project
         */
        $project = $subject;

        $member = $project->findMember($user);
        if (!$member) {
            return false;
        }

        return match (true) {
            in_array($attribute, $this->developerAttributes()) => $member->isDeveloper(),
            default => true
        };
    }

    private function developerAttributes(): array
    {
        return [
            self::STORY_POINTS_SET,
            self::UPDATE_ISSUE_TAGS,
            self::ASSIGNEE_SET,
            self::UPDATE_ISSUE_TITLE,
            self::UPDATE_ISSUE_DESCRIPTION
        ];
    }
}
