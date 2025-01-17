<?php

namespace App\Entity\Issue;

use App\Entity\Thread\ThreadMessage;
use App\Repository\Issue\IssueThreadMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueThreadMessageRepository::class)]
class IssueThreadMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'issueThreadMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue = null;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private ?ThreadMessage $threadMessage = null;

    public function __construct(
        Issue $issue,
        ThreadMessage $threadMessage
    ) {
        $this->issue = $issue;
        $this->threadMessage = $threadMessage;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function getThreadMessage(): ?ThreadMessage
    {
        return $this->threadMessage;
    }
}
