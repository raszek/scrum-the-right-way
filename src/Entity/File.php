<?php

namespace App\Entity;

use App\Repository\FileRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $directory = null;

    #[ORM\Column(length: 255)]
    private ?string $mime = null;

    #[ORM\Column(length: 255)]
    private ?string $extension = null;

    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public static function fromUploadedFile(
        UploadedFile $uploadedFile,
        string $directory,
        DateTimeImmutable $createdAt
    ): static {
        return new static(
            name: $uploadedFile->getClientOriginalName(),
            directory: $directory,
            mime: $uploadedFile->getMimeType(),
            extension: $uploadedFile->getClientOriginalExtension(),
            size: $uploadedFile->getSize(),
            createdAt: $createdAt
        );
    }

    public function __construct(
        string $name,
        string $directory,
        string $mime,
        string $extension,
        string $size,
        DateTimeImmutable $createdAt
    ) {
        $this->name = $name;
        $this->directory = $directory;
        $this->mime = $mime;
        $this->extension = $extension;
        $this->size = $size;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function getPath(): string
    {
        return $this->directory.'/'.$this->getName();
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'png', 'gif']);
    }

    public function isVideo(): bool
    {
        return in_array($this->extension, ['mp4']);
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
}
