<?php

namespace App\Entity\User;

use App\Repository\User\UserCodeRepository;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCodeRepository::class)]
class UserCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $mainUser = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $data = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct(
        User $user,
        string $type,
        string $code,
        DateTimeImmutable $createdAt,
        ?array $data = null
    ) {
        $this->mainUser = $user;
        $this->type = $type;
        $this->code = $code;
        $this->createdAt = $createdAt;
        $this->data = $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->mainUser;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getCreatedAt(): CarbonImmutable
    {
        return CarbonImmutable::instance($this->createdAt);
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
