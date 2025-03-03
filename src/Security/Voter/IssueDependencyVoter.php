<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IssueDependencyVoter extends Voter
{
    public const ISSUE_ADD_DEPENDENCY = 'ISSUE_ADD_DEPENDENCY';

    public const ISSUE_REMOVE_DEPENDENCY = 'ISSUE_REMOVE_DEPENDENCY';

    public const ISSUE_LIST_DEPENDENCIES = 'ISSUE_LIST_DEPENDENCIES';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::ISSUE_ADD_DEPENDENCY,
            self::ISSUE_REMOVE_DEPENDENCY,
            self::ISSUE_LIST_DEPENDENCIES
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
