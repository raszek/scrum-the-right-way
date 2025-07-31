<?php

namespace App\Action\Profile;

use App\Entity\User\User;
use App\Form\Profile\AvatarFormData;
use App\Service\File\FileService;
use App\Service\Image\ImageEditorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class ChangeAvatar
{

    public function __construct(
        private FileService $fileService,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private ImageEditorFactory $imageEditorFactory,
    ) {
    }

    public function execute(AvatarFormData $formData, User $user): array
    {
        $this->updateAvatar($formData, $user);

        $this->entityManager->flush();
        
        return $this->getResult($user);
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
        $imageEditor->resize(height: 64);
    }

    private function suffixThumb(string $filename): string
    {
        $parts = explode('.', $filename);

        $extension = array_pop($parts);

        return implode('.', $parts) . '_thumb.' . $extension;
    }

    private function getResult(User $user): ?array
    {
        $avatar = $user->getProfile()->getAvatar();

        if (!$avatar) {
            return null;
        }

        return [
            'id' => $avatar->getId(),
            'url' => $this->urlGenerator->generate('app_user_profile_show_avatar', ['id' => $user->getId()]),
        ];
    }
}
