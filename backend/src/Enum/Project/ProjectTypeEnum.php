<?php

namespace App\Enum\Project;

use App\Helper\ArrayHelper;

enum ProjectTypeEnum: int
{
    case Kanban = 1;

    case Scrum = 2;

    /**
     * @return string[]
     */
    public static function keys(): array
    {
        return ArrayHelper::map(
            ProjectTypeEnum::cases(),
            fn(ProjectTypeEnum $type) => $type->key(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            ProjectTypeEnum::Scrum => 'Scrum',
            ProjectTypeEnum::Kanban => 'Kanban',
        };
    }

    public function key(): string
    {
        return match ($this) {
            ProjectTypeEnum::Scrum => 'scrum',
            ProjectTypeEnum::Kanban => 'kanban',
        };
    }

    public static function fromKey(string $key): ProjectTypeEnum
    {
        return match ($key) {
            'scrum' => ProjectTypeEnum::Scrum,
            'kanban' => ProjectTypeEnum::Kanban,
        };
    }
}
