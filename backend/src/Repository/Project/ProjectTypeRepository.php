<?php

namespace App\Repository\Project;

use App\Entity\Project\ProjectType;
use App\Enum\Project\ProjectTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectType>
 */
class ProjectTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectType::class);
    }

    public function scrumType(): ProjectType
    {
        return $this->getReference(ProjectTypeEnum::Scrum);
    }

    public function kanbanType(): ProjectType
    {
        return $this->getReference(ProjectTypeEnum::Kanban);
    }

    public function getReference(ProjectTypeEnum $enum): ProjectType
    {
        return $this->getEntityManager()->getReference(ProjectType::class, $enum->value);
    }
}
