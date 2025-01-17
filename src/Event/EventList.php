<?php

namespace App\Event;

interface EventList
{

    /**
     * @return array<string, string>
     */
    public static function rendererClasses(): array;


    /**
     * @return array<string, string>
     */
    public function labels(): array;
}
