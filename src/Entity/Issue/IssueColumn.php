<?php

namespace App\Entity\Issue;

use App\Enum\Issue\IssueColumnEnum;
use App\Repository\Issue\IssueColumnRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueColumnRepository::class)]
class IssueColumn
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

    public function isBacklog(): bool
    {
        return $this->isColumn(IssueColumnEnum::Backlog);
    }

    public function isToDo(): bool
    {
        return $this->isColumn(IssueColumnEnum::ToDo);
    }

    public function isInProgress(): bool
    {
        return $this->isColumn(IssueColumnEnum::InProgress);
    }

    public function isTest(): bool
    {
        return $this->isColumn(IssueColumnEnum::Test);
    }

    public function isTested(): bool
    {
        return $this->isColumn(IssueColumnEnum::Tested);
    }

    public function isColumn(IssueColumnEnum $issueColumnEnum): bool
    {
        return $this->getId() === $issueColumnEnum->value;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
