<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IssueVoter extends Voter
{
    public const KANBAN_VIEW = 'KANBAN_VIEW';

    public const BACKLOG_VIEW = 'BACKLOG_VIEW';

    public const LIST_ISSUES = 'LIST_ISSUES';

    public const CREATE_ISSUE = 'CREATE_ISSUE';

    public const VIEW_ISSUE = 'VIEW_ISSUE';


    protected function supports(string $attribute, mixed $subject): bool
    {
        $actions = [
            self::KANBAN_VIEW,
            self::BACKLOG_VIEW,
            self::CREATE_ISSUE,
            self::VIEW_ISSUE,
            self::LIST_ISSUES,
        ];

        return in_array($attribute, $actions) && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User || !$subject instanceof Project) {
            return false;
        }

        $foundMember = $subject->findMember($user);
        if (!$foundMember) {
            return false;
        }

        return match (true) {
            $attribute === self::CREATE_ISSUE => $foundMember->isDeveloper(),
            default => true
        };
    }

}
