<?php

namespace App\Entity\Room;

use App\Doctrine\Sqid;
use App\Entity\Issue\Issue;
use App\Repository\Room\RoomIssueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomIssueRepository::class)]
class RoomIssue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\ManyToOne(inversedBy: 'roomIssues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Issue $issue = null;

    public function __construct(
        Room $room,
        Issue $issue
    ) {
        $this->room = $room;
        $this->issue = $issue;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }
}
