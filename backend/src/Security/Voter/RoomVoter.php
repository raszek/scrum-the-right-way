<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class RoomVoter extends Voter
{
    public const string VIEW_ROOM = 'VIEW_ROOM';

    public const string CREATE_ROOM = 'CREATE_ROOM';

    public const string VIEW_ROOM_ISSUE = 'VIEW_ROOM_ISSUE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::VIEW_ROOM,
            self::CREATE_ROOM,
            self::VIEW_ROOM_ISSUE
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

        $foundMember = $subject->findMember($user);

        return $foundMember && $foundMember->isDeveloper();
    }
}
