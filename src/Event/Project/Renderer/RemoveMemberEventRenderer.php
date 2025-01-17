<?php

namespace App\Event\Project\Renderer;

use App\Entity\Event\Event;
use App\Entity\User\User;
use App\Event\EventRendererInterface;
use App\Event\Project\Event\RemoveMemberEvent;

readonly class RemoveMemberEventRenderer implements EventRendererInterface
{
    public function __construct(
        private MemberEventRenderer $memberEventRenderer
    ) {
    }

    public function fetch(array $events): array
    {
        return $this->memberEventRenderer->fetch($events, $this->render(...));
    }

    private function render(Event $event, User $removedMemberUser): string
    {
        $user = $event->getCreatedBy();

        return sprintf(
            '<b>%s</b> has removed user <b>%s</b> from project',
            $user->getFullName(),
            $removedMemberUser->getFullName()
        );
    }

    public function eventDataClass(): string
    {
        return RemoveMemberEvent::class;
    }
}
