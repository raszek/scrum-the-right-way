<?php

namespace App\Security\Voter;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectMemberVoter extends Voter
{
    public const MEMBER_LIST = 'MEMBER_LIST';

    public const PROJECT_MEMBER_ADD_ROLE = 'PROJECT_MEMBER_ADD_ROLE';

    public const PROJECT_MEMBER_REMOVE_ROLE = 'PROJECT_MEMBER_REMOVE_ROLE';

    public const PROJECT_ADD_MEMBER = 'PROJECT_ADD_MEMBER';

    public const PROJECT_REMOVE_MEMBER = 'PROJECT_REMOVE_MEMBER';

    public const PROJECT_SEARCH_NON_MEMBER = 'PROJECT_SEARCH_NON_MEMBER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = [
            self::MEMBER_LIST,
            self::PROJECT_MEMBER_ADD_ROLE,
            self::PROJECT_MEMBER_REMOVE_ROLE,
            self::PROJECT_ADD_MEMBER,
            self::PROJECT_REMOVE_MEMBER,
            self::PROJECT_SEARCH_NON_MEMBER
        ];

        return in_array($attribute, $attributes) && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /**
         * @var Project $project
         */
        $project = $subject;

        if ($attribute === self::MEMBER_LIST) {
            return $project->hasMember($user);
        }

        $projectMember = $project->findMember($user);

        return $projectMember && $projectMember->isAdmin();
    }
}
