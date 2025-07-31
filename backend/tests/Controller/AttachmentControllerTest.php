<?php

namespace App\Tests\Controller;

use App\Entity\File;
use App\Factory\FileFactory;
use App\Factory\Issue\AttachmentFactory;
use App\Factory\Issue\IssueColumnFactory;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueTypeFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\Project\ProjectMemberFactory;
use App\Factory\Project\ProjectMemberRoleFactory;
use App\Factory\Project\ProjectRoleFactory;
use App\Factory\UserFactory;
use App\Repository\FileRepository;
use App\Repository\Issue\AttachmentRepository;
use App\Service\File\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AttachmentControllerTest extends WebTestCase
{

    /** @test */
    public function developer_can_add_attachments_to_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        $this->loginAsUser($user);

        $fileName = 'cat.jpg';
        $imagePath = $this->temporaryFromDataFile($fileName);

        $uploadedFile = new UploadedFile($imagePath, $fileName);

        $crawler = $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/attachments', files: [
            'file' => $uploadedFile
        ]);

        $this->assertResponseStatusCodeSame(201);

        $id = $crawler->children()->children()->attr('data-dropzone-item-id-param');

        $createdAttachment = $this->attachmentRepository()->findOneBy([
            'id' => $id
        ]);

        $this->assertNotNull($createdAttachment);
        $this->assertEquals($issue->getId(), $createdAttachment->getIssue()->getId());
    }

    /** @test */
    public function developer_can_remove_attachment_from_issue()
    {
        $client = static::createClient();
        $client->followRedirects();

        $user = UserFactory::createOne();

        $project = ProjectFactory::createOne([
            'code' => 'SCP'
        ]);

        $memberDeveloper = ProjectMemberFactory::createOne([
            'user' => $user,
            'project' => $project
        ]);

        $developerRole = ProjectRoleFactory::developerRole();

        ProjectMemberRoleFactory::createOne([
            'projectMember' => $memberDeveloper,
            'role' => $developerRole
        ]);

        $backlogColumn = IssueColumnFactory::backlogColumn();

        $issueType = IssueTypeFactory::issueType();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'issueColumn' => $backlogColumn,
            'type' => $issueType,
            'number' => 12,
        ]);

        /**
         * @var File $file
         */
        $file = FileFactory::new()
            ->withPath($this->dataFileName('cat.jpg'))
            ->create();

        $filePathToBeRemoved = $this->fileService()->getFilePath($file);
        $fileId = $file->getId();

        $attachment = AttachmentFactory::createOne([
            'issue' => $issue,
            'file' => $file
        ]);

        $attachmentId = $attachment->getId();

        $this->loginAsUser($user);

        $client->request('POST', '/projects/' . $project->getId() . '/issues/SCP-12/attachments/'.$attachment->getId().'/remove');

        $this->assertResponseStatusCodeSame(204);

        $removedAttachment = $this->attachmentRepository()->findOneBy([
            'id' => $attachmentId
        ]);

        $this->assertNull($removedAttachment);

        $removedFile = $this->fileRepository()->findOneBy([
            'id' => $fileId
        ]);

        $this->assertNull($removedFile);
        $this->assertFileDoesNotExist($filePathToBeRemoved);
    }

    protected function tearDown(): void
    {
        $this->cleanUploadDirectory();

        parent::tearDown();
    }

    private function fileService(): FileService
    {
        return $this->getService(FileService::class);
    }

    private function attachmentRepository(): AttachmentRepository
    {
        return $this->getService(AttachmentRepository::class);
    }

    private function fileRepository(): FileRepository
    {
        return $this->getService(FileRepository::class);
    }
}
