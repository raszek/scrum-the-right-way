<?php

namespace App\Service\Common;

readonly class ProjectDirectory
{

    public function __construct(
        private string $rootPath,
        private string $uploadDirectory
    ) {
    }

    public function uploadDirectoryPath(): string
    {
        return $this->rootPath.'/'.$this->uploadDirectory;
    }

}
