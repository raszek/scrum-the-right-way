<?php

namespace App\Entity\Profile;

use App\Entity\File;
use App\Repository\Profile\ProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?File $avatar = null;

    #[ORM\ManyToOne]
    private ?File $avatarThumb = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAvatar(): ?File
    {
        return $this->avatar;
    }

    public function setAvatar(?File $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarThumb(): ?File
    {
        return $this->avatarThumb;
    }

    public function setAvatarThumb(?File $avatarThumb): static
    {
        $this->avatarThumb = $avatarThumb;

        return $this;
    }
}
