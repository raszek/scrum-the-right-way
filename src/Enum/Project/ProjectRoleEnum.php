<?php

namespace App\Enum\Project;

enum ProjectRoleEnum: int
{
    case Admin = 1;

    case Analytic = 2;

    case ScrumMaster = 3;

    case Developer = 4;

    case Tester = 5;

    public function label(): string
    {
        return match ($this) {
            ProjectRoleEnum::Admin => 'Admin',
            ProjectRoleEnum::Analytic => 'Analytic',
            ProjectRoleEnum::Developer => 'Developer',
            ProjectRoleEnum::Tester => 'Tester',
            ProjectRoleEnum::ScrumMaster => 'Scrum master'
        };
    }


    public static function isKanbanRole(ProjectRoleEnum $role): bool
    {
        return in_array($role, self::kanbanRoles());
    }

    /**
     * @return ProjectRoleEnum[]
     */
    public static function scrumRoles(): array
    {
        return [
            ...self::kanbanRoles(),
            ProjectRoleEnum::ScrumMaster
        ];
    }

    /**
     * @return ProjectRoleEnum[]
     */
    public static function kanbanRoles(): array
    {
        return [
            ProjectRoleEnum::Admin,
            ProjectRoleEnum::Analytic,
            ProjectRoleEnum::Developer,
            ProjectRoleEnum::Tester
        ];
    }

}
