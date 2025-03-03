<?php

namespace App\Enum\Project;

enum ProjectRoleEnum: int
{
    case Admin = 1;

    case Developer = 2;

    public function label(): string
    {
        return match ($this) {
            ProjectRoleEnum::Admin => 'Admin',
            ProjectRoleEnum::Developer => 'Developer',
        };
    }
}
