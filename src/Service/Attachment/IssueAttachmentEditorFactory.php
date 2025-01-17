<?php

namespace App\Service\Attachment;

use App\Entity\Issue\Issue;
use App\Service\File\FileService;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueAttachmentEditorFactory
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileService $fileService
    ) {
    }

    public function create(Issue $issue): IssueAttachmentEditor
    {
        return new IssueAttachmentEditor(
            issue: $issue,
            entityManager: $this->entityManager,
            fileService: $this->fileService,
        );
    }

}
