<?php

namespace App\Event\Project\Renderer;

use App\Entity\Event\Event;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Event\EventRecord;
use App\Event\EventRendererInterface;
use App\Event\Project\Event\AddRoleEvent;

readonly class AddRoleEventRenderer implements EventRendererInterface
{

    public function __construct(
        private RoleEventRenderer $roleEventRenderer
    ) {
    }

    /**
     * @param Event[] $events
     * @return EventRecord[]
     */
    public function fetch(array $events): array
    {
        return $this->roleEventRenderer->fetch($events, $this->render(...));
    }

    private function render(Event $event, User $userWithChangedRole, ProjectRoleEnum $roleEnum): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has added role <b>%s</b> to user <b>%s</b>',
            $user->getFullName(),
            $roleEnum->label(),
            $userWithChangedRole->getFullName()
        );
    }

    public function eventDataClass(): string
    {
        return AddRoleEvent::class;
    }
}
