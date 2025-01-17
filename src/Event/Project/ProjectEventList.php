<?php

namespace App\Event\Project;


use App\Event\EventList;
use App\Event\Project\Renderer\AddMemberEventRenderer;
use App\Event\Project\Renderer\AddRoleEventRenderer;
use App\Event\Project\Renderer\RemoveMemberEventRenderer;
use App\Event\Project\Renderer\RemoveRoleEventRenderer;

class ProjectEventList implements EventList
{

    const PROJECT_MEMBER_ADD_ROLE = 'PROJECT_MEMBER_ADD_ROLE';

    const PROJECT_MEMBER_REMOVE_ROLE = 'PROJECT_MEMBER_REMOVE_ROLE';

    const PROJECT_REMOVE_MEMBER = 'PROJECT_REMOVE_MEMBER';

    const PROJECT_ADD_MEMBER = 'PROJECT_ADD_MEMBER';

    public static function rendererClasses(): array
    {
        return [
            self::PROJECT_MEMBER_ADD_ROLE => AddRoleEventRenderer::class,
            self::PROJECT_MEMBER_REMOVE_ROLE => RemoveRoleEventRenderer::class,
            self::PROJECT_REMOVE_MEMBER => RemoveMemberEventRenderer::class,
            self::PROJECT_ADD_MEMBER => AddMemberEventRenderer::class
        ];
    }

    public function labels(): array
    {
        return [
            'Add project member role' => self::PROJECT_MEMBER_ADD_ROLE,
            'Remove project member role' => self::PROJECT_MEMBER_REMOVE_ROLE,
            'Remove project member' => self::PROJECT_REMOVE_MEMBER,
            'Add project member' => self::PROJECT_ADD_MEMBER
        ];
    }
}
