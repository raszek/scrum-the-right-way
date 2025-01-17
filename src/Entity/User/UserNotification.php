<?php

namespace App\Entity\User;

use App\Doctrine\Sqid;
use App\Entity\Event\Event;
use App\Repository\User\UserNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserNotificationRepository::class)]
class UserNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $forUser = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $isSentEmail = false;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $isRead = false;

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function __construct(
        Event $event,
        User $forUser
    ) {
        $this->event = $event;
        $this->forUser = $forUser;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getForUser(): ?User
    {
        return $this->forUser;
    }

    public function isSentEmail(): ?bool
    {
        return $this->isSentEmail;
    }

    public function setSentEmail(bool $isSentEmail): static
    {
        $this->isSentEmail = $isSentEmail;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }
}
