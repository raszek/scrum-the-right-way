<?php

namespace App\Entity\Issue;

use App\Enum\Issue\IssueTypeEnum;
use App\Repository\Issue\IssueTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueTypeRepository::class)]
class IssueType
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

    public function isFeature(): bool
    {
        return $this->getId() === IssueTypeEnum::Feature->value;
    }
}
