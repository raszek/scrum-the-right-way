<?php

namespace App\Entity\Project;

use App\Doctrine\Sqid;
use App\Repository\Project\ProjectTagRepository;
use App\ValueObject\Color;
use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;

#[ORM\UniqueConstraint(columns: ['project_id', 'name'])]
#[ORM\Entity(repositoryClass: ProjectTagRepository::class)]
class ProjectTag
{

    const NAME_REGEX = '/^[A-Za-z_]+$/';

    const NAME_MAX_LENGTH = 30;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $backgroundColor;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    public function __construct(
        string $name,
        Color $backgroundColor,
        Project $project
    ) {
        Assertion::regex($name, self::NAME_REGEX);
        Assertion::maxLength($name, self::NAME_MAX_LENGTH);

        $this->project = $project;
        $this->name = $name;
        $this->backgroundColor = $backgroundColor->formatHex();
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBackgroundColor(): string
    {
        return Color::fromHex($this->backgroundColor)->formatHex();
    }
}
