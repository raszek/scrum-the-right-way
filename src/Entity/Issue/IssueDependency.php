<?php

namespace App\Entity\Issue;

use App\Repository\Issue\IssueDependencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueDependencyRepository::class)]
class IssueDependency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'issueDependencies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $dependency = null;

    public function __construct(
        Issue $issue,
        Issue $dependency
    ) {
        $this->issue = $issue;
        $this->dependency = $dependency;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }
    public function getDependency(): ?Issue
    {
        return $this->dependency;
    }
}
