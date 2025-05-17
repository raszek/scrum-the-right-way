<?php

namespace App\Entity\Issue;

use App\Doctrine\Sqid;
use App\Entity\File;
use App\Repository\Issue\AttachmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttachmentRepository::class)]
class Attachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\ManyToOne(inversedBy: 'attachments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $file = null;

    public function __construct(
        Issue $issue,
        File $file
    ) {
        $this->issue = $issue;
        $this->file = $file;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function canBeDisplayed(): bool
    {
        return $this->file->isImage() || $this->file->isVideo();
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }
}
