<?php

namespace App\Event\Project\Renderer;

use App\Entity\Event\Event;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Event\EventRendererInterface;
use App\Event\Project\Event\RemoveRoleEvent;

readonly class RemoveRoleEventRenderer implements EventRendererInterface
{

    public function __construct(
        private RoleEventRenderer $roleEventRenderer,
    ) {
    }

    public function fetch(array $events): array
    {
        return $this->roleEventRenderer->fetch($events, $this->render(...));
    }

    private function render(Event $event, User $userWithChangedRole, ProjectRoleEnum $roleEnum): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has removed role <b>%s</b> from user <b>%s</b>',
            $user->getFullName(),
            $roleEnum->label(),
            $userWithChangedRole->getFullName()
        );
    }
    public function eventDataClass(): string
    {
        return RemoveRoleEvent::class;
    }
}
