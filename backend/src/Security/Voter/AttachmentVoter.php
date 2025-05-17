<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AttachmentVoter extends Voter
{

    public const VIEW_ATTACHMENT = 'VIEW_ATTACHMENT';

    public const CREATE_ATTACHMENT = 'CREATE_ATTACHMENT';

    public const REMOVE_ATTACHMENT = 'REMOVE_ATTACHMENT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::CREATE_ATTACHMENT,
            self::REMOVE_ATTACHMENT,
            self::VIEW_ATTACHMENT
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

        if ($attribute === self::VIEW_ATTACHMENT) {
            return true;
        }

        return $member->isDeveloper();
    }
}
