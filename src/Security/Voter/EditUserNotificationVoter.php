<?php

namespace App\Security\Voter;

use App\Entity\User\User;
use App\Entity\User\UserNotification;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditUserNotificationVoter extends Voter
{
    public const MARK_READ_NOTIFICATION = 'MARK_READ_NOTIFICATION';

    public const MARK_UNREAD_NOTIFICATION = 'MARK_UNREAD_NOTIFICATION';

    public const MARK_ALL_READ_NOTIFICATION = 'MARK_ALL_READ_NOTIFICATION';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::MARK_READ_NOTIFICATION,
            self::MARK_UNREAD_NOTIFICATION,
            self::MARK_ALL_READ_NOTIFICATION
        ];

        return in_array($attribute, $attributes) && $subject instanceof UserNotification;
    }

    /**
     * @param string $attribute
     * @param UserNotification $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $subject->getForUser()->getId() === $user->getId();
    }
}
