<?php

namespace App\Enum\Project;

enum ProjectTypeEnum: int
{
    case Kanban = 1;

    case Scrum = 2;


    public function label(): string
    {
        return match ($this) {
            ProjectTypeEnum::Scrum => 'Scrum',
            ProjectTypeEnum::Kanban => 'Kanban',
        };
    }

}
