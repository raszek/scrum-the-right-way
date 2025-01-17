<?php

namespace App\Entity\Thread;

use App\Repository\Thread\ThreadStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadStatusRepository::class)]
class ThreadStatus
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
