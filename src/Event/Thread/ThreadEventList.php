<?php

namespace App\Event\Thread;

use App\Event\EventList;
use App\Event\Thread\Renderer\AddThreadMessageEventRenderer;
use App\Event\Thread\Renderer\CloseThreadEventRenderer;
use App\Event\Thread\Renderer\CreateThreadEventRenderer;
use App\Event\Thread\Renderer\OpenThreadEventRenderer;

class ThreadEventList implements EventList
{

    const THREAD_CREATE = 'THREAD_CREATE';
    const THREAD_CLOSE = 'THREAD_CLOSE';
    const THREAD_OPEN = 'THREAD_OPEN';
    const THREAD_ADD_MESSAGE = 'THREAD_ADD_MESSAGE';

    /**
     * @return array<string, string>
     */
    public static function rendererClasses(): array
    {
        return [
            self::THREAD_CREATE => CreateThreadEventRenderer::class,
            self::THREAD_CLOSE => CloseThreadEventRenderer::class,
            self::THREAD_OPEN => OpenThreadEventRenderer::class,
            self::THREAD_ADD_MESSAGE => AddThreadMessageEventRenderer::class
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'Create thread' => self::THREAD_CREATE,
            'Close thread' => self::THREAD_CLOSE,
            'Open thread' => self::THREAD_OPEN,
            'Add thread message' => self::THREAD_ADD_MESSAGE
        ];
    }
}
