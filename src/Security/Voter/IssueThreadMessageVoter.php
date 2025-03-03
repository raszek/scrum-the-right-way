<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IssueThreadMessageVoter extends Voter
{
    public const ISSUE_ADD_THREAD_MESSAGE = 'ISSUE_ADD_THREAD_MESSAGE';

    public const ISSUE_REMOVE_THREAD_MESSAGE = 'ISSUE_REMOVE_THREAD_MESSAGE';

    public const ISSUE_LIST_THREAD_MESSAGES = 'ISSUE_LIST_THREAD_MESSAGES';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::ISSUE_ADD_THREAD_MESSAGE,
            self::ISSUE_REMOVE_THREAD_MESSAGE,
            self::ISSUE_LIST_THREAD_MESSAGES
        ];


        return in_array($attribute, $attributes) && $subject instanceof Project;
    }

    /**
     * @param string $attribute
     * @param Project $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $member = $subject->findMember($user);
        if (!$member) {
            return false;
        }

        return $member->isDeveloper();
    }
}
