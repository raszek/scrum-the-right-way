<?php

namespace App\Form\Profile;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AvatarFormData
{

    public function __construct(
        public ?UploadedFile $avatar = null,
    ) {
    }

}
