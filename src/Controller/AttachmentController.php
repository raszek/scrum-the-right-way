<?php

namespace App\Controller;

use App\Entity\Issue\Attachment;
use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Form\Attachment\AttachmentForm;
use App\Repository\Issue\AttachmentRepository;
use App\Repository\Issue\IssueRepository;
use App\Security\Voter\AttachmentVoter;
use App\Service\Attachment\IssueAttachmentEditorFactory;
use App\Service\Common\RandomService;
use App\Service\File\FileService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/projects/{id}/issues/{issueCode}')]
class AttachmentController extends CommonIssueController
{

    public function __construct(
        IssueRepository $issueRepository,
        private readonly ValidatorInterface $validator,
        private readonly IssueAttachmentEditorFactory $issueAttachmentEditorFactory,
        private readonly AttachmentRepository $attachmentRepository,
        private readonly FileService $fileService,
        private readonly RandomService $randomService
    ) {
        parent::__construct($issueRepository);
    }

    #[Route('/attachments/{attachmentId}', name: 'app_project_issue_view_attachment', methods: ['GET'])]
    public function view(Project $project, string $issueCode, string $attachmentId): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted(AttachmentVoter::VIEW_ATTACHMENT, $project);

        $issue = $this->findIssue($issueCode, $project);

        $attachment = $this->findAttachment($attachmentId, $issue);

        $filePath = $this->fileService->getFilePath($attachment->getFile());

        return new BinaryFileResponse($filePath);
    }

    #[Route('/attachments', name: 'app_project_issue_add_attachment', methods: ['POST'])]
    public function create(Project $project, string $issueCode, Request $request): Response
    {
        $this->denyAccessUnlessGranted(AttachmentVoter::CREATE_ATTACHMENT, $project);

        $issue = $this->findIssue($issueCode, $project);

        $attachmentForm = AttachmentForm::fromRequest($request);

        $errors = $this->validator->validate($attachmentForm);

        if (count($errors) > 0) {
            return $this->displayUploadError($attachmentForm->uploadedFile, $errors);
        }

        $issueAttachmentEditor = $this->issueAttachmentEditorFactory->create($issue);

        $attachment = $issueAttachmentEditor->createAttachment($attachmentForm);

        return $this->render('attachment/attachment.html.twig', [
            'attachment' => $attachment,
            'project' => $project,
            'issue' => $issue
        ], new Response(status: Response::HTTP_CREATED));
    }

    #[Route('/attachments/{attachmentId}/remove', name: 'app_project_issue_remove_attachment', methods: ['POST'])]
    public function remove(Project $project, string $issueCode, string $attachmentId): Response
    {
        $this->denyAccessUnlessGranted(AttachmentVoter::REMOVE_ATTACHMENT, $project);

        $issue = $this->findIssue($issueCode, $project);

        $issueAttachmentEditor = $this->issueAttachmentEditorFactory->create($issue);

        $attachment = $this->findAttachment($attachmentId, $issue);

        $issueAttachmentEditor->removeAttachment($attachment);

        return new Response(status: 204);
    }

    private function findAttachment(string $attachmentId, Issue $issue): Attachment
    {
        $attachment = $this->attachmentRepository->findOneBy([
            'id' => $attachmentId,
            'issue' => $issue
        ]);

        if (!$attachment) {
            throw new NotFoundHttpException('Attachment not found');
        }

        return $attachment;
    }

    private function displayUploadError(UploadedFile $file, ConstraintViolationList $errors): Response
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[] = $error->getMessage();
        }

        return $this->render('attachment/attachment_error.html.twig', [
            'itemId' => $this->randomService->randomString(6),
            'mainError' => 'Could not upload '. $file->getClientOriginalName(),
            'errors' => $formattedErrors,
            'filename' => $file->getClientOriginalName()
        ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
