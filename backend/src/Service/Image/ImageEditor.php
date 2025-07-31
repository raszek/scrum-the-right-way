<?php

namespace App\Service\Image;

use Intervention\Image\ImageManager;

readonly class ImageEditor
{

    public function __construct(
        private string $imagePath,
        private ImageManager $manager
    ) {
    }

    public function resize(?int $width = null, ?int $height = null): void
    {
        $image = $this->manager->read($this->imagePath);

        $image->resize($width, $height);

        $image->save($this->imagePath);
    }
}
