<?php

namespace App\Entity\Issue;

use App\Doctrine\Sqid;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectTag;
use App\Entity\User\User;
use App\Enum\Issue\IssueColumnEnum;
use App\Repository\Issue\IssueRepository;
use App\Service\Position\Positionable;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
#[ORM\UniqueConstraint(columns: ['project_id', 'number'])]
class Issue implements Positionable
{

    const DEFAULT_ORDER_SPACE = 1024;

    const TITLE_LENGTH = 2048;

    const MAX_TAG_COUNT = 20;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'sqid')]
    private ?Sqid $id = null;

    #[ORM\Column]
    private int $number;

    #[ORM\Column(length: self::TITLE_LENGTH)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $storyPoints = null;

    #[ORM\Column()]
    private int $columnOrder;

    #[ORM\Column(nullable: true)]
    private ?int $issueOrder = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private IssueColumn $issueColumn;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private IssueType $type;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $createdBy;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProjectMember $assignee = null;

    #[ORM\ManyToOne(inversedBy: 'subIssues')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Issue $parent = null;

    /**
     * @var Collection<int, DescriptionHistory>
     */
    #[ORM\OneToMany(targetEntity: DescriptionHistory::class, mappedBy: 'issue')]
    private Collection $descriptionHistories;

    /**
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(targetEntity: Attachment::class, mappedBy: 'issue')]
    private Collection $attachments;

    /**
     * @var Collection<int, IssueObserver>
     */
    #[ORM\OneToMany(targetEntity: IssueObserver::class, mappedBy: 'issue')]
    private Collection $issueObservers;

    /**
     * @var Collection<int, IssueThreadMessage>
     */
    #[ORM\OneToMany(targetEntity: IssueThreadMessage::class, mappedBy: 'issue')]
    private Collection $issueThreadMessages;

    /**
     * @var Collection<int, ProjectTag>
     */
    #[ORM\ManyToMany(targetEntity: ProjectTag::class)]
    private Collection $tags;

    /**
     * @var Collection<int, IssueDependency>
     */
    #[ORM\OneToMany(targetEntity: IssueDependency::class, mappedBy: 'issue')]
    private Collection $issueDependencies;

    /**
     * @var Collection<int, Issue>
     */
    #[ORM\OneToMany(targetEntity: Issue::class, mappedBy: 'parent')]
    private Collection $subIssues;

    public function __construct(
        int               $number,
        string            $title,
        int               $columnOrder,
        IssueColumn       $issueColumn,
        IssueType         $type,
        Project           $project,
        User              $createdBy,
        DateTimeImmutable $createdAt,
        ?Issue $parent = null,
        ?int $issueOrder = null
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->columnOrder = $columnOrder;
        $this->issueColumn = $issueColumn;
        $this->project = $project;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->setType($type);
        $this->setIssueOrder($issueOrder);
        $this->setParent($parent);
        $this->descriptionHistories = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->issueObservers = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->issueThreadMessages = new ArrayCollection();
        $this->issueDependencies = new ArrayCollection();
        $this->subIssues = new ArrayCollection();
    }

    public function getId(): ?Sqid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTitleWithCode(): string
    {
        return $this->getTitle().' #'.$this->number;
    }

    public function prefixCodeTitle(): string
    {
        return sprintf('[#%d] %s', $this->number, $this->title);
    }

    public function fullText(): string
    {
        return sprintf(
            '[#%d] [%s] %s',
            $this->number,
            $this->getType()->getLabel(),
            $this->title
        );
    }

    public function getShortTitle($maxCharacterCount = 75): string
    {
        $words = explode(' ', $this->title);

        $shortTitle = [];
        $characterCount = 0;
        foreach ($words as $word) {
            $characterCount += mb_strlen($word);
            $shortTitle[] = $word;
            if ($characterCount > $maxCharacterCount) {
                return implode(' ', $shortTitle).'...';
            }
        }

        return implode(' ', $shortTitle);
    }

    public function isOnBacklogColumn(): bool
    {
        return $this->getIssueColumn()->isBacklog();
    }

    public function isOnColumn(IssueColumnEnum $columnEnum): bool
    {
        return $this->getIssueColumn()->getId() === $columnEnum->value;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAssignee(): ?ProjectMember
    {
        return $this->assignee;
    }

    public function setAssignee(?ProjectMember $projectMember): void
    {
        $this->assignee = $projectMember;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIssueColumn(): ?IssueColumn
    {
        return $this->issueColumn;
    }

    public function setIssueColumn(?IssueColumn $issueColumn): static
    {
        $this->issueColumn = $issueColumn;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCode(): string
    {
        return $this->project->getCode().'-'.$this->getNumber();
    }

    public function getBracketCode(): string
    {
        return sprintf('[%s]', $this->getCode());
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setColumnOrder(int $order): static
    {
        $this->columnOrder = $order;

        return $this;
    }

    public function getColumnOrder(): ?int
    {
        return $this->columnOrder;
    }

    public function getType(): IssueType
    {
        return $this->type;
    }

    public function setType(IssueType $type): void
    {
        $this->type = $type;
    }

    public function getStoryPoints(): ?int
    {
        return $this->storyPoints;
    }

    public function setStoryPoints(?int $storyPoints): void
    {
        $this->storyPoints = $storyPoints;
    }

    /**
     * @return Collection<int, DescriptionHistory>
     */
    public function getDescriptionChanges(): Collection
    {
        return $this->descriptionHistories;
    }

    /**
     * @return Collection<int, Attachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    /**
     * @return Collection<int, IssueObserver>
     */
    public function getObservers(): Collection
    {
        return $this->issueObservers;
    }

    public function isObservedBy(ProjectMember $member): bool
    {
        return $this->issueObservers->exists(
            fn(int $i, IssueObserver $observer) => $observer->getProjectMember()->getId() === $member->getId()
        );
    }

    public function addObserver(IssueObserver $issueObserver): void
    {
        $this->issueObservers->add($issueObserver);
    }

    public function removeObserver(IssueObserver $issueObserver): void
    {
        $this->issueObservers->removeElement($issueObserver);
    }

    /**
     * @return Collection<int, ProjectTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ProjectTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(ProjectTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return Collection<int, IssueThreadMessage>
     */
    public function getIssueThreadMessages(): Collection
    {
        return $this->issueThreadMessages;
    }

    public function removeMessage(IssueThreadMessage $issueMessage): void
    {
        $this->issueThreadMessages->removeElement($issueMessage);
    }

    /**
     * @return Collection<int, IssueDependency>
     */
    public function getIssueDependencies(): Collection
    {
        return $this->issueDependencies;
    }

    public function addIssueDependency(IssueDependency $issueDependency): void
    {
        $this->issueDependencies->add($issueDependency);
    }

    public function removeIssueDependency(IssueDependency $issueDependency): void
    {
        $this->issueDependencies->removeElement($issueDependency);
    }

    /**
     * @return Collection<Issue>
     */
    public function getSubIssues(): Collection
    {
        return $this->subIssues;
    }

    public function getLastSubIssue(): ?Issue
    {
        $lastIssue = $this->subIssues->last();

        if (!$lastIssue) {
            return null;
        }

        return $lastIssue;
    }

    public function getParent(): ?Issue
    {
        return $this->parent;
    }

    public function isFeature(): bool
    {
        return $this->getType()->isFeature();
    }

    public function isSubIssue(): bool
    {
        return $this->getType()->isSubIssue();
    }

    public function hasEnabledSubIssues(): bool
    {
        return $this->isFeature();
    }

    public function getIssueOrder(): ?int
    {
        return $this->issueOrder;
    }

    public function setIssueOrder(?int $issueOrder): void
    {
        if ($this->type->isSubIssue() && $issueOrder === null) {
            throw new RuntimeException('Sub issues must have issue order.');
        }

        $this->issueOrder = $issueOrder;
    }

    public function getOrder(): int
    {
        return $this->getColumnOrder();
    }

    public function setOrder(int $order): void
    {
        $this->setColumnOrder($order);
    }

    public function getOrderSpace(): int
    {
        return self::DEFAULT_ORDER_SPACE;
    }

    private function setParent(?Issue $parent): void
    {
        if ($this->isSubIssue() && $parent === null) {
            throw new RuntimeException('Sub issue must have parent issue.');
        }

        $this->parent = $parent;
    }


    public function canEditStoryPoints(): bool
    {
        return !$this->isFeature();
    }
}
