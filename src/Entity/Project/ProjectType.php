<?php

namespace App\Entity\Project;

use App\Enum\Project\ProjectTypeEnum;
use App\Repository\Project\ProjectTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectTypeRepository::class)]
class ProjectType
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    private ?string $label;

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

    public function isScrum(): bool
    {
        return $this->id === ProjectTypeEnum::Scrum->value;
    }

    public function isKanban(): bool
    {
        return $this->id === ProjectTypeEnum::Kanban->value;
    }
}
