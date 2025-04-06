<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class KanbanVoter extends Voter
{

    public const KANBAN_VIEW = 'KANBAN_VIEW';

    public const KANBAN_MOVE_ISSUE = 'KANBAN_MOVE_ISSUE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::KANBAN_VIEW,
            self::KANBAN_MOVE_ISSUE
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
            default => true
        };
    }

    /**
     * @return string[]
     */
    private function developerAttributes(): array
    {
        return [
            self::KANBAN_MOVE_ISSUE
        ];
    }
}
