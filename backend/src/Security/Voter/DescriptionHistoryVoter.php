<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DescriptionHistoryVoter extends Voter
{

    public const ISSUE_DESCRIPTION_HISTORY_LIST = 'ISSUE_DESCRIPTION_HISTORY_LIST';

    public const ISSUE_DESCRIPTION_HISTORY_VIEW = 'ISSUE_DESCRIPTION_HISTORY_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::ISSUE_DESCRIPTION_HISTORY_LIST,
            self::ISSUE_DESCRIPTION_HISTORY_VIEW,
        ];

        return in_array($attribute, $attributes)
            && $subject instanceof Project;
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

        return $subject->hasMember($user);
    }
}
