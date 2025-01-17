<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ThreadVoter extends Voter
{
    public const THREAD_LIST = 'THREAD_LIST';

    public const THREAD_CREATE = 'THREAD_CREATE';

    public const THREAD_MESSAGES = 'THREAD_MESSAGES';

    public const THREAD_CLOSE = 'THREAD_CLOSE';

    public const THREAD_REOPEN = 'THREAD_REOPEN';


    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::THREAD_LIST,
            self::THREAD_CREATE,
            self::THREAD_MESSAGES,
            self::THREAD_CLOSE,
            self::THREAD_REOPEN,
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

        return $subject->hasMember($user);
    }
}
