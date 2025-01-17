<?php

namespace App\Entity\Issue;

use App\Entity\Project\ProjectMember;
use App\Repository\Issue\IssueObserverRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueObserverRepository::class)]
#[ORM\UniqueConstraint(columns: ['project_member_id', 'id'])]
class IssueObserver
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'issueObservers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProjectMember $projectMember = null;

    public function __construct(
        Issue $issue,
        ProjectMember $projectMember
    ) {
        $this->issue = $issue;
        $this->projectMember = $projectMember;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function getProjectMember(): ?ProjectMember
    {
        return $this->projectMember;
    }

    public function getFullName(): string
    {
        return $this->projectMember->getFullName();
    }
}
