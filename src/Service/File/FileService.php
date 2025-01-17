<?php

namespace App\Service\File;

use App\Entity\File;
use App\Service\Common\ClockInterface;
use App\Service\Common\ProjectDirectory;
use App\Service\Common\RandomService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RandomService $randomService,
        private ClockInterface $clock,
        private ProjectDirectory $projectDirectory
    ) {
    }

    public function uploadFromUploadedFile(UploadedFile $uploadedFile): File
    {
        $directory = $this->generateDirectory();

        $directoryPath = $this->generateDirectoryPath($directory);

        $file = File::fromUploadedFile(
            uploadedFile: $uploadedFile,
            directory: $directory,
            createdAt: $this->clock->now()
        );

        $uploadedFile->move($directoryPath, $file->getName());

        $this->entityManager->persist($file);

        return $file;
    }

    public function removeFile(File $file): void
    {
        $this->removeFileSource($file);

        $this->entityManager->remove($file);
    }

    public function getFilePath(File $file): string
    {
        return $this->projectDirectory->uploadDirectoryPath().'/'.$file->getPath();
    }

    private function getFileDirectory(File $file): string
    {
        return $this->projectDirectory->uploadDirectoryPath().'/'.$file->getDirectory();
    }

    private function generateDirectoryPath(string $directory): string
    {
        return $this->projectDirectory->uploadDirectoryPath().'/'.$directory;
    }

    private function generateDirectory(): string
    {
        return $this->randomService->randomString(48);
    }

    private function removeFileSource(File $file): void
    {
        unlink($this->getFilePath($file));

        rmdir($this->getFileDirectory($file));
    }
}
