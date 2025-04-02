<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SprintVoter extends Voter
{
    public const ADD_CURRENT_SPRINT_ISSUE = 'ADD_CURRENT_SPRINT_ISSUE';

    public const REMOVE_CURRENT_SPRINT_ISSUE = 'REMOVE_CURRENT_SPRINT_ISSUE';

    public const MOVE_CURRENT_SPRINT_ISSUE = 'MOVE_CURRENT_SPRINT_ISSUE';

    public const REMOVE_CURRENT_SPRINT_GOAL = 'REMOVE_CURRENT_SPRINT_GOAL';

    public const EDIT_SPRINT_GOAL = 'EDIT_SPRINT_GOAL';

    public const SORT_SPRINT_GOAL = 'SORT_SPRINT_GOAL';

    public const VIEW_CURRENT_SPRINT = 'VIEW_SPRINT';


    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::ADD_CURRENT_SPRINT_ISSUE,
            self::VIEW_CURRENT_SPRINT,
            self::REMOVE_CURRENT_SPRINT_ISSUE,
            self::REMOVE_CURRENT_SPRINT_GOAL,
            self::EDIT_SPRINT_GOAL,
            self::MOVE_CURRENT_SPRINT_ISSUE,
            self::SORT_SPRINT_GOAL
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

        return match (true) {
            in_array($attribute, $this->developerAttributes()) => $member->isDeveloper(),
            default => true,
        };
    }

    /**
     * @return string[]
     */
    private function developerAttributes(): array
    {
        return [
            self::ADD_CURRENT_SPRINT_ISSUE,
            self::REMOVE_CURRENT_SPRINT_ISSUE,
            self::REMOVE_CURRENT_SPRINT_GOAL,
            self::EDIT_SPRINT_GOAL,
            self::MOVE_CURRENT_SPRINT_ISSUE,
            self::SORT_SPRINT_GOAL
        ];
    }
}
