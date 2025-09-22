<?php

namespace App\Action\Profile;

use App\Entity\Profile\Profile;
use App\Entity\User\User;
use App\Form\Profile\AvatarFormData;
use App\Service\File\FileService;
use App\Service\Image\ImageEditorFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class ChangeAvatar
{
    public function __construct(
        private FileService $fileService,
        private EntityManagerInterface $entityManager,
        private ImageEditorFactory $imageEditorFactory,
    ) {
    }

    public function execute(AvatarFormData $formData, User $user): Profile
    {
        $this->updateAvatar($formData, $user);

        $this->entityManager->flush();

        return $user->getProfile();
    }

    private function updateAvatar(AvatarFormData $formData, User $user): void
    {
        $profile = $user->getProfile();

        if ($profile->getAvatar()) {
            $this->fileService->removeFile($profile->getAvatar());
        }

        if ($profile->getAvatarThumb()) {
            $this->fileService->removeFile($profile->getAvatarThumb());
        }

        if ($formData->avatar === null) {
            $profile->setAvatar(null);
            $profile->setAvatarThumb(null);
            return;
        }

        $avatarFile = $this->fileService->uploadFromUploadedFile($formData->avatar);
        $profile->setAvatar($avatarFile);

        $thumbFileName = $this->suffixThumb($avatarFile->getName());
        $avatarThumbFile = $this->fileService->copyFile($avatarFile, $thumbFileName);
        $profile->setAvatarThumb($avatarThumbFile);

        $imageEditor = $this->imageEditorFactory->create($this->fileService->getFilePath($avatarThumbFile));
        $imageEditor->resize(width: 64, height: 64);
    }

    private function suffixThumb(string $filename): string
    {
        $parts = explode('.', $filename);

        $extension = array_pop($parts);

        return implode('.', $parts) . '_thumb.' . $extension;
    }
}
