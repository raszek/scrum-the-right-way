<?php

namespace App\Entity\Project;

use App\Enum\Project\ProjectRoleEnum;
use App\Repository\Project\ProjectMemberRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectMemberRoleRepository::class)]
class ProjectMemberRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ProjectRole $role;

    #[ORM\ManyToOne(inversedBy: 'roles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ProjectMember $projectMember;

    public function __construct(
        ProjectRole $role,
        ProjectMember $projectMember
    ) {
        $this->role = $role;
        $this->projectMember = $projectMember;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?ProjectRole
    {
        return $this->role;
    }

    public function setRole(?ProjectRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function isRole(ProjectRoleEnum $role): bool
    {
        return $this->role->getId() === $role->value;
    }

    public function isAdmin(): bool
    {
        return $this->isRole(ProjectRoleEnum::Admin);
    }

    public function getProjectMember(): ?ProjectMember
    {
        return $this->projectMember;
    }

    public function setProjectMember(?ProjectMember $projectMember): static
    {
        $this->projectMember = $projectMember;

        return $this;
    }
}
