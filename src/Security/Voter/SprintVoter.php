<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SprintVoter extends Voter
{
    public const ADD_CURRENT_SPRINT_ISSUE = 'ADD_CURRENT_SPRINT_ISSUE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::ADD_CURRENT_SPRINT_ISSUE
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
