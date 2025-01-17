<?php

namespace App\Event\Thread\Renderer;

use App\Entity\Event\Event;
use App\Entity\Thread\Thread;
use App\Event\EventRendererInterface;
use App\Event\RendererUrlGenerator;
use App\Event\Thread\Event\CreateThreadEvent;

readonly class CreateThreadEventRenderer implements EventRendererInterface
{

    public function __construct(
        private ThreadEventRenderer $threadEventRenderer,
        private RendererUrlGenerator $urlGenerator
    ) {
    }


    public function fetch(array $events): array
    {
        return $this->threadEventRenderer->fetch($events, $this->render(...));
    }

    private function render(Event $event, Thread $thread): string
    {
        $user = $event->getCreatedBy();
        $project = $event->getProject();

        $url = $this->urlGenerator->generate('app_project_thread_messages', [
            'id' => $project->getId(),
            'threadId' => $thread->getId(),
            'slug' => $thread->getSlug()
        ]);

        return sprintf(
            '<b>%s</b> has created thread <a href="%s">%s</a>',
            $user->getFullName(),
            $url,
            $thread->getTitle()
        );
    }

    public function eventDataClass(): string
    {
        return CreateThreadEvent::class;
    }
}
