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

    public function findScrum(): ProjectType
    {
        return $this->findOneBy([
            'id' => ProjectTypeEnum::Scrum->value
        ]);
    }

    public function findKanban(): ProjectType
    {
        return $this->findOneBy([
            'id' => ProjectTypeEnum::Kanban->value
        ]);
    }
}
