<?php

namespace App\Tests\Controller;

use App\Entity\User\User;
use App\Service\Common\ProjectDirectory;
use App\Service\Common\RandomService;
use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Zenstruck\Foundry\Test\Factories;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use Factories;

    public function dataFileName(string $fileName): string
    {
        return __DIR__.'/../_data/'.$fileName;
    }

    public function temporaryFromDataFile(string $sourcePath): string
    {
        $code = $this->randomService()->randomString(6);

        $source = $this->dataFileName($sourcePath);
        $fileName = pathinfo($source, PATHINFO_FILENAME);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $destinationPath = $this->dataFileName($fileName.'-'.$code.'.'.$extension);

        $isSuccess = copy($source, $destinationPath);

        if ($isSuccess === false) {
            throw new Exception(sprintf('Could not copy from %s to %s', $source, $destinationPath));
        }

        return $destinationPath;
    }

    public function loginAsUser(User $user): void
    {
        $userClass = get_class($user);

        if ($userClass !== User::class) {
            self::getClient()->loginUser($user->_real());
        } else {
            self::getClient()->loginUser($user);
        }
    }
    
    public function goToPage(string $page): Crawler
    {
        return self::getClient()->request('GET', $page);
    }

    public function goToPageSafe(string $page): Crawler
    {
        $crawler = $this->goToPage($page);

        $this->assertResponseIsSuccessful();

        return $crawler;
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return T
     */
    public function getService(string $className): mixed
    {
        return self::getClient()->getContainer()->get($className);
    }

    public function mockService(string $className, mixed $mockObject): void
    {
        self::getClient()->getContainer()->set($className, $mockObject);
    }

    public function readTable(string $selector): array
    {
        $crawler = $this->getCrawler();

        return $crawler->filter($selector)->filter('tr')->each(function ($tr) {
            return $tr->filter('td,th')->each(function ($td) {
                return trim($td->text());
            });
        });
    }

    public function assertResponseHasText(string $text): void
    {
        $this->assertTrue(
            $this->doesResponseHasText($text),
            sprintf('Page does not contain "%s", but it should.', $text)
        );
    }

    public function assertResponseHasNoText(string $text): void
    {
        $this->assertFalse(
            $this->doesResponseHasText($text),
            sprintf('Page does contain "%s", but it should not.', $text)
        );
    }

    public function doesResponseHasText($text): bool
    {
        $crawler = $this->getCrawler();

        return str_contains($crawler->text(), $text) ;
    }

    public function assertPath(string $expectedPath): void
    {
        $crawler = $this->getCrawler();

        $parsedUrl = parse_url($crawler->getUri());

        if (!isset($parsedUrl['path'])) {
            throw new Exception('Current url does not contain path');
        }

        $this->assertEquals(
            $parsedUrl['path'],
            $expectedPath,
            sprintf('Current path "%s" does not match expected path %s', $parsedUrl['path'], $expectedPath)
        );
    }

    public function cleanUploadDirectory(): void
    {
        $filesystem = new Filesystem();

        $finder = new Finder();

        $files = $finder->files()
            ->notName('.gitkeep')
            ->in($this->projectDirectory()->uploadDirectoryPath());

        foreach ($files as $file) {
            $filesystem->remove($file->getPath());
        }
    }

    private function getCrawler(): Crawler
    {
        return self::getClient()->getCrawler();
    }

    private function randomService(): RandomService
    {
        return $this->getService(RandomService::class);
    }

    private function projectDirectory(): ProjectDirectory
    {
        return $this->getService(ProjectDirectory::class);
    }
}
