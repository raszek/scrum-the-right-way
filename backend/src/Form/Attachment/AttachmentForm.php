<?php

namespace App\Form\Attachment;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

readonly class AttachmentForm
{

    const EXTENSIONS = [
        // text
        'xml',
        'json',
        'txt',
        // image
        'jpg',
        'png',
        'gif',
        // video
        'mp4',
        // document
        'pdf',
    ];

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\File(
            maxSize: '16Mi',
            extensions: self::EXTENSIONS,
        )]
        public ?UploadedFile $uploadedFile = null
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            uploadedFile: $request->files->get('file')
        );
    }
}
