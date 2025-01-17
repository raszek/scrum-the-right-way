<?php

namespace App\Service\Attachment;

use App\Entity\Issue\Attachment;
use App\Entity\Issue\Issue;
use App\Form\Attachment\AttachmentForm;
use App\Service\File\FileService;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueAttachmentEditor
{

    public function __construct(
        private Issue $issue,
        private EntityManagerInterface $entityManager,
        private FileService $fileService
    ) {
    }

    public function createAttachment(AttachmentForm $attachmentForm): Attachment
    {
        $file = $this->fileService->uploadFromUploadedFile($attachmentForm->uploadedFile);

        $attachment = new Attachment(
            issue: $this->issue,
            file: $file
        );

        $this->entityManager->persist($attachment);

        $this->entityManager->flush();

        return $attachment;
    }

    public function removeAttachment(Attachment $attachment): void
    {
        $this->fileService->removeFile($attachment->getFile());

        $this->entityManager->remove($attachment);

        $this->entityManager->flush();
    }
}
