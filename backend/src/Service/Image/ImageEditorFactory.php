<?php

namespace App\Service\Image;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageEditorFactory
{

    public function create(string $imagePath): ImageEditor
    {
        $manager = new ImageManager(
            new Driver()
        );

        return new ImageEditor(
            imagePath: $imagePath,
            manager: $manager,
        );
    }

}
