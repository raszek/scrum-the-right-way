<?php

namespace App\Factory;

use App\Entity\File;
use App\Service\Common\ProjectDirectory;
use App\Service\Common\RandomService;
use DateTimeImmutable;
use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<File>
 */
final class FileFactory extends PersistentProxyObjectFactory
{

    public function __construct(
        private readonly ProjectDirectory $projectDirectory,
        private readonly RandomService $randomService
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return File::class;
    }

    public function withPath(string $path): self
    {
        $filesystem = new Filesystem();

        $directory = $this->generateDirectory();

        $pathInfo = pathinfo($path);

        $destinationFile = $this->projectDirectory->uploadDirectoryPath().'/'.$directory.'/'.$pathInfo['basename'];
        $filesystem->copy($path, $destinationFile);

        return $this->with([
            'name' => $pathInfo['basename'],
            'directory' => $directory,
            'extension' => $pathInfo['extension'],
            'mime' => mime_content_type($path),
            'size' => filesize($path)
        ]);
    }

    protected function defaults(): array|callable
    {
        $extension = self::faker()->randomElement(['jpg', 'jpeg', 'png', 'gif', 'docx', 'doc', 'pdf', 'xls', 'xlsx']);

        return [
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'directory' => $this->generateDirectory(),
            'extension' => $extension,
            'mime' => self::faker()->text(255),
            'name' => self::faker()->word().'.'.$extension,
            'size' => self::faker()->randomNumber(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this;
    }

    private function generateDirectory(): string
    {
        return $this->randomService->randomString(48);
    }
}
