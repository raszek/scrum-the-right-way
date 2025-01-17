<?php

namespace App\Entity\Project;

use App\Repository\Project\ProjectRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRoleRepository::class)]
class ProjectRole
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    public function __construct(
        int $id,
        string $label
    ) {
        $this->id = $id;
        $this->label = $label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
