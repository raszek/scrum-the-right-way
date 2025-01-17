<?php

namespace App\Repository\Project;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectTag;
use App\Helper\ArrayHelper;
use App\View\Issue\SelectedTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectTag>
 */
class ProjectTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectTag::class);
    }

    /**
     * @param Project $project
     * @param Issue $issue
     * @return SelectedTag[]
     */
    public function selectedTags(Project $project, Issue $issue): array
    {
        $projectTags = $this->findBy([
            'project' => $project,
        ]);

        return ArrayHelper::map($projectTags, function (ProjectTag $projectTag) use ($issue) {
            return new SelectedTag(
                name: $projectTag->getName(),
                isSelected: $issue->getTags()->exists(fn($i, ProjectTag $pt) => $pt->getId() === $projectTag->getId()),
                backgroundColor: $projectTag->getBackgroundColor()
            );
        });
    }
}
