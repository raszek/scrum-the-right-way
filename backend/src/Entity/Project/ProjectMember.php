<?php

namespace App\Entity\Project;

use App\Doctrine\Sqid;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Repository\Project\ProjectMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectMemberRepository::class)]
class ProjectMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project;

    #[ORM\ManyToOne(inversedBy: 'projectMembers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    /**
     * @var Collection<int, ProjectMemberRole>
     */
    #[ORM\OneToMany(targetEntity: ProjectMemberRole::class, mappedBy: 'projectMember', orphanRemoval: true)]
    private Collection $roles;

    public function __construct(
        Project $project,
        User $user
    ) {
        $this->project = $project;
        $this->user = $user;
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return Collection<int, ProjectMemberRole>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(ProjectMemberRole $role): void
    {
        $this->roles->add($role);
    }

    public function removeRole(ProjectMemberRole $role): void
    {
        $this->roles->removeElement($role);
    }

    public function hasRole(ProjectRoleEnum $searchedRole): bool
    {
        foreach ($this->roles as $role) {
            if ($role->getRole()->getId() === $searchedRole->value) {
                return true;
            }
        }

        return false;
    }

    public function canToggleRole(ProjectMember $member, ProjectRoleEnum $role): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        if ($member->getId() === $this->getId()) {
            return $role !== ProjectRoleEnum::Admin;
        }

        return true;
    }

    public function canRemoveMembers(): bool
    {
        return $this->isAdmin();
    }

    public function getFullName(): string
    {
        return $this->getUser()->getFullName();
    }

    public function getEmail(): string
    {
        return $this->getUser()->getEmail();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(ProjectRoleEnum::Admin);
    }

    public function isDeveloper(): bool
    {
        return $this->hasRole(ProjectRoleEnum::Developer);
    }
}
