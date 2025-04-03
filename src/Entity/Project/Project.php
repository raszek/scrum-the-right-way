<?php

namespace App\Entity\Project;

use App\Doctrine\Sqid;
use App\Entity\Sprint\Sprint;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Repository\Project\ProjectRepository;
use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{

    const CODE_REGEX = '/^[A-Z]{3}$/';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 40)]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: self::CODE_REGEX,
        message: 'Code must be exactly 3 BIG letters'
    )]
    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProjectType $type = null;

    #[ORM\OneToMany(targetEntity: ProjectMember::class, mappedBy: 'project')]
    private Collection $members;

    #[ORM\OneToMany(targetEntity: Sprint::class, mappedBy: 'project')]
    private Collection $sprints;

    #[ORM\OneToMany(targetEntity: ProjectTag::class, mappedBy: 'project')]
    private Collection $tags;

    public function __construct(
        string $name,
        string $code,
        ProjectType $type,
    ) {
        $this->members = new ArrayCollection();
        $this->sprints = new ArrayCollection();
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?ProjectType
    {
        return $this->type;
    }

    public function isScrum(): bool
    {
        return $this->getType()->isScrum();
    }

    public function isKanban(): bool
    {
        return $this->getType()->isKanban();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        Assertion::regex($code, self::CODE_REGEX);

        $this->code = $code;
    }

    /**
     * @return Collection<ProjectMember>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function getAdmins(): Collection
    {
        return $this->members->filter(fn(ProjectMember $member) => $member->isAdmin());
    }

    /**
     * @return ProjectRoleEnum[]
     */
    public function memberRoleTypes(): array
    {
        return ProjectRoleEnum::cases();
    }

    public function findMember(User $searchedUser): ?ProjectMember
    {
        foreach ($this->getMembers() as $member) {
            if ($searchedUser->getId() === $member->getUser()->getId()) {
                return $member;
            }
        }

        return null;
    }

    public function member(User $user): ProjectMember
    {
        $member = $this->findMember($user);

        if (!$member) {
            throw new Exception('Member not found');
        }

        return $member;
    }

    public function hasMember(User $searchedMember): bool
    {
        return $this->findMember($searchedMember) !== null;
    }

    /**
     * @return Collection<Sprint>
     */
    public function getSprints(): Collection
    {
        return $this->sprints;
    }

    /**
     * @return Collection<ProjectTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

}
